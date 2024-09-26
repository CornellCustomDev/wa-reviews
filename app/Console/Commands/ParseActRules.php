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
        $files = Storage::files('act-rules.github.io-develop/_rules');

        $headings = $this->option('headings');
        if ($headings) {
            $this->info('Finding all of the headings');
            $this->findHeadings($files);
        } else {
            $this->info('Truncating the act_rules table');
            ActRule::truncate();
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
        foreach ($files as $file) {
            // Split the metadata and markdown content
            $content = Storage::get($file);
            if (preg_match('/^---\s*(.*?)\s*---\s*(.*)$/s', $content, $matches)) {
                $yamlContent = $matches[1];
                $yamlData = Yaml::parse($yamlContent);
                $markdownContent = $matches[2];
                if (preg_match('/(\n\[.*?]:\s*.*?)(?=\n\n|\z)/s', $markdownContent)) {
                    $markdownContent = preg_replace('/(\n\[.*?]:\s*.*?)(?=\n\n|\z)/s', '', $markdownContent);
                }
            } else {
                // Report that the file is not in the expected format
                $this->error('File ' . $file . ' is not in the expected format');
                continue;
            }

            $rule = new ActRule();
            $rule->id = $yamlData['id'];
            $rule->machine_name = pathinfo($file, PATHINFO_FILENAME);
            $rule->name = $yamlData['name'];
            $rule->metadata = $yamlData;
            $rule->markdown = $markdownContent;
            $rule->test_cases = $this->parseTestCases($markdownContent);
            $rule->save();
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

    private function parseTestCases(string $markdownContent): array
    {
        $casesContent = $this->getMarkdownSection($markdownContent, 'Test Cases');
        $test_cases = [];
        while (preg_match('/^####\s*(.*?)\s*$(.*?)```html\n(.*?)\n```/ms', $casesContent, $matches)) {
            $name = $matches[1];
            $description = trim($matches[2]);
            $html = $matches[3];
            // Remove the matched test case from the string
            $casesContent = str_replace($matches[0], '', $casesContent);

            // get the first word of the name
            $type = explode(' ', $name)[0];
            // match to the test case type
            $testType = match($type) {
                'Passed' => 'passed',
                'Failed' => 'failed',
                'Inapplicable' => 'inapplicable',
                default => 'unknown',
            };
            $test_cases[$testType][] = [
                'name' => $name,
                'description' => $description,
                'html' => $html,
            ];
        }

        return $test_cases;
    }
}
