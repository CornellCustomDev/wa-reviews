<?php

namespace App\Console\Commands;

use App\Models\ActRule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;

class ParseActRules extends Command
{
    protected $signature = 'app:parse-act-rules {--headings}';

    public function handle()
    {
        $directory = 'act-rules.github.io-develop/_rules';
        // Get the list of files in the directory
        $files = Storage::files($directory);

        $headings = $this->option('headings');
        if ($headings) {
            $this->info('Finding all of the headings');
            $this->findHeadings($files);
        } else {
            $this->info('Truncating the act_rules table');
            ActRule::truncate();
            $this->info('Empty the act-rules-yaml directory');
            Storage::deleteDirectory('act-rules-yaml');
            $this->info('Parsing all of the rules');
            $this->parseRules($files);
            // Find out how many rules were parsed
            $this->info('Parsed ' . ActRule::count() . ' rules');
        }
    }

    private function findHeadings($files): void
    {
        $yamlFieldCounts = [];
        $headingCounts = [];

        foreach ($files as $file) {
            $content = Storage::get($file);
            if (preg_match('/^---\s*(.*?)\s*---\s*(.*)$/s', $content, $matches)) {
                $yamlContent = $matches[1];
                $markdownContent = $matches[2];
            } else {
                // Report that the file is not in the expected format
                $this->error('File ' . $file . ' is not in the expected format');
                continue;
            }

            // Get the keys of the yaml data
            $yamlKeys = array_keys(Yaml::parse($yamlContent));
            foreach ($yamlKeys as $key) {
                data_set($yamlFieldCounts, $key, data_get($yamlFieldCounts, $key, 0) + 1);
            }

            // Get the second-level headings in the markdown content
            preg_match_all('/^##\s*(.*?)\s*$/m', $markdownContent, $matches);
            foreach ($matches[1] as $heading) {
                if (str_starts_with($heading, '#')) {
                    continue;
                }
                data_set($headingCounts, $heading, data_get($headingCounts, $heading, 0) + 1);
            }
        }

        // Output the yaml field counts as a table
        dd($yamlFieldCounts, $headingCounts);
    }

    private function parseRules($files): void
    {
        $keys = ['Applicability', 'Assumptions', 'Accessibility Support', 'Background', 'Test Cases'];
        $expectation_keys = ['Expectation', 'Expectation 1', 'Expectation 2', 'Expectation 3', 'Expectations'];

        foreach ($files as $file) {
            $content = Storage::get($file);
            if (preg_match('/^---\s*(.*?)\s*---\s*(.*)$/s', $content, $matches)) {
                $yamlContent = $matches[1];
                $markdownContent = $matches[2];
                if (preg_match('/(\n\[.*?]:\s*.*?)(?=\n\n|\z)/s', $markdownContent)) {
                    $markdownContent = preg_replace('/(\n\[.*?]:\s*.*?)(?=\n\n|\z)/s', '', $markdownContent);
                }
            } else {
                // Report that the file is not in the expected format
                $this->error('File ' . $file . ' is not in the expected format');
                continue;
            }

            // Get the ruleName from the filename without the extension
            $ruleName = pathinfo($file, PATHINFO_FILENAME);

            $yamlData = Yaml::parse($yamlContent);

            $rule = new ActRule();
            $rule->id = $yamlData['id'];
            $rule->filename = $ruleName;
            $rule->name = $yamlData['name'];

            $rule->metadata = $yamlData;

            // Get the key markdown sections
            foreach ($keys as $key) {
                $fieldname = str_replace(' ', '_', strtolower($key));
                $rule->$fieldname = $this->getMarkdownSection($markdownContent, $key);
            }

            $expectations = '';
            foreach ($expectation_keys as $key) {
                $expectations .= $this->getMarkdownSection($markdownContent, $key);
            }
            $rule->expectation = $expectations;

            $rule->save();

            // Also store the $rule as yaml in /act-rules-yaml/{id}.yaml
            echo $rule->name . " ({$yamlData['id']})\n";
            $yaml = Yaml::dump([
                'id' => $yamlData['id'],
                'name' => $rule->name,
                'metadata' => $rule->metadata,
                'applicability' => $rule->applicability,
                'assumptions' => $rule->assumptions,
                'accessibility_support' => $rule->accessibility_support,
                'background' => $rule->background,
                'test_cases' => $rule->getTestCases(),
                'expectation' => $rule->expectation,
            ]);
            Storage::put('act-rules-yaml/' . $ruleName . '.yaml', $yaml);
        }
    }

    private function getMarkdownSection(string $content, string $heading): string
    {
        // Build a regex pattern to match the heading and capture the content
        $pattern = '/^(#{1,6})\s+' . preg_quote($heading, '/') . '\s*$([\s\S]*?)(?=^\1\s+|\z)/m';

        // Explanation of the regex pattern:
        // - ^(#{1,6})\s+       : Matches the start of a line with 1 to 6 '#' symbols (capturing the heading level in group 1), followed by whitespace.
        // - preg_quote(...)    : Escapes any special characters in the target heading to safely include it in the pattern.
        // - \s*$               : Matches any whitespace till the end of the line.
        // - ([\s\S]*?)         : Non-greedy match to capture all content (including newlines) up to the next heading.
        // - (?=^\1\s+|\z)      : Positive lookahead to stop matching when it encounters a heading of the same level or the end of the string.
        // - /m                 : Multiline mode, so ^ and $ match the start and end of lines.

        if (preg_match($pattern, $content, $matches)) {
            $content = trim($matches[2]);
            // Remove glossary references
            return preg_replace('/\[(.*?)]\[.*?]/', '$1', $content);
        }

        return '';
    }
}
