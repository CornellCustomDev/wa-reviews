<div class="cwd-component">
    <div class="align-right">
        <x-forms.link-button route="{{ route('guidelines.index') }}" title="All Guidelines" />
    </div>

    <div class="metadata-set metadata-blocks accent-red-dark">
        <h1>
            <a href="{{ $guideline->reference_url }}" target="_blank">
                <span class="deco fa fa-bookmark">
                    {{ $guideline->number }}
                </span>
            </a>
        </h1>
    </div>

    <h2>
        {{ $guideline->name }}
    </h2>

    <table class="table bordered">
        <tr>
            <th style="width: 200px">WGAC 2 criterion</th>
            <td><a href="{{ route('criteria.show', [$guideline->criterion]) }}">{{ $guideline->criterion->getLongName() }}</a></td>
        </tr>
        <tr>
            <th>Category</th>
            <td>
                <a href="{{ route('categories.show', $guideline->category) }}">
                    {{ $guideline->category->name }}
                </a>
            </td>
        </tr>
        <tr>
            <th>ACT Rules</th>
            <td>
                @if($guideline->actRules->isNotEmpty())
                    <ul>
                        @foreach($guideline->actRules as $rule)
                            <li><a href="{{ route('act-rules.show', $rule) }}">{{ $rule->name }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </td>
        </tr>
    </table>

    {!! Str::markdown($guideline->notes) !!}


    <h2>AI Prompt</h2>
    <aside class="panel">
        <button style="float:right" onclick="navigator.clipboard.writeText(document.getElementById('ai-prompt').innerText).then(() => {alert('Prompt copied to clipboard!')})">Copy to Clipboard</button>
<pre id="ai-prompt">Create a PHP class in the namespace "App\Services\AccessibilityAnalyzer\GuidelineRules"
that extends "App\Services\AccessibilityAnalyzer\GuidelineRuleRunner" and is called "Guideline{{ $guideline->id }}" that has a function with the signature "protected function findApplicableElements(Crawler $crawler): Crawler". The base class includes the function "protected function isElementIncludedInAccessibilityTree(\DOMNode $element): bool" for determining if an element is included in the accessibility tree.

Web accessibility guideline:

## {{ $guideline->name }}

{!! e($guideline->notes) !!}
</pre>

    </aside>
</div>

{{-- Sidebar for AI help --}}
<x-slot name="sidebarPrimary">
    <livewire:ai.guideline-chat :$guideline />
</x-slot>
