<?php

namespace App\Console\Commands;

use App\Enums\GuidelineTools;
use App\Models\Guideline;
use Illuminate\Console\Command;

class ParseGuidelineTools extends Command
{
    protected $signature = 'app:parse-guideline-tools';

    public function handle()
    {
        $guidelines = Guideline::all();

        foreach ($guidelines as $guideline) {
            $tools = [];
            $content = $guideline->notes;

            if (preg_match('/### Tools and requirements\s*(.*?)\s*###/s', $content, $matches)) {
                // Convert the list into an array
                $tools = array_map('trim', explode("\n", $matches[1]));
                // Remove any empty elements
                $tools = array_filter($tools, function($tool) {
                    return !empty($tool);
                });
                // Parse the tools into a list of standard tool names
                $tools = array_map(function($tool) {
                    $tool = strtolower($tool);
                    return match (true) {
                        ($tool == '* siteimprove'),
                        ($tool == '* siteimprove (next-gen)'),
                        ($tool == '* siteimprove helps (policy)'),
                        str_contains($tool, '* siteimprove (policy)') => GuidelineTools::Siteimprove,
                        str_contains($tool, '* manual') => GuidelineTools::Manual,
                        ($tool == '* keyboard') => GuidelineTools::Keyboard,
                        ($tool == '* screen reader') => GuidelineTools::ScreenReader,
                        ($tool == '* axe extension'),
                        ($tool == '* axe'),
                        ($tool == '* andi'),
                        ($tool == '* wave extension'),
                        ($tool == '* wave, axe accessibility checker'),
                        ($tool == '* wave') => GuidelineTools::BrowserExtension,
                        ($tool == '* color contrast analyzer'),
                        ($tool == '* color contrast analyzer plugin'),
                        ($tool == '* color contrast analyzer tool') => GuidelineTools::ColorContrastAnalyzer,
                        default => GuidelineTools::Other,
                    };
                }, $tools);
                // Remove any duplicates
                $tools = array_unique($tools);
            } else {
                // Report any guidelines that don't have tools
                $this->warn('No tools found for guideline ' . $guideline->number);
            }

            $guideline->tools = $tools;
            $guideline->save();
        }
    }
}
