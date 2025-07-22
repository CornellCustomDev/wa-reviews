@props([
    'issue',
])
<div class="inline-flex flex-nowrap items-center gap-x-0.5">
    @if($issue->assessment && $issue->guideline)
        <flux:tooltip position="bottom" class="align-middle">
            <x-forms.button
                data-cds-button-assessment
                class="{{ Str::of($issue->assessment->value())->lower()->replace('/', '') }}"
                size="xs"
                x-on:click.prevent="$dispatch('show-guideline', {number: {{ $issue->guideline->number }} })"
            >
                {{ $issue->guideline->getNumber() }}
            </x-forms.button>

            <flux:tooltip.content class="max-w-[600px]">
                @if($issue->guideline->number < 100)
                    <p>{{ $issue->assessment->getDescription() }} for Guideline {{ $issue->guideline->number }}
                        : {{ $issue->guideline->getCriterionInfo() }} - {{ $issue->guideline->name }}</p>
                @else
                    <p>{{ $issue->assessment->getDescription() }} for Best
                        Practice: {{ $issue->guideline->name }}</p>
                @endif
            </flux:tooltip.content>
        </flux:tooltip>
   @endif

    @if($issue->content_issue)
        <flux:tooltip toggleable position="right" class="align-middle">
            <flux:button icon="information-circle" size="sm" variant="ghost" class="text-cds-blue-600!"/>
            <flux:tooltip.content class="max-w-[20rem] space-y-2">
                <p>Content entry issue</p>
            </flux:tooltip.content>
        </flux:tooltip>
    @endif

    @if($issue->impact)
        <flux:badge data-cds-impact class="{{ Str::of($issue->impact->value())->lower() }}" size="sm">
            {{ $issue->impact->value() }}
        </flux:badge>
    @endif

    @if($issue->isAiGenerated())
        <flux:tooltip toggleable position="right" class="align-middle">
            @if($issue->hasUnreviewedAi())
                <flux:button icon="sparkles" size="xs" variant="ghost" class="text-red-600!"/>
            @elseif($issue->isAiAccepted())
                <flux:button icon="sparkles" size="xs" variant="ghost" class="text-cds-blue-600!"/>
            @endif
            <flux:tooltip.content class="max-w-[20rem] space-y-2">
                @if($issue->hasUnreviewedAi())
                    <flux:heading class="text-white">This AI-generated response needs to be reviewed</flux:heading>
                @endif
                <flux:subheading class="text-white">AI Reasoning</flux:subheading>
                <blockquote class="mb-0 pl-2">{!! $issue->ai_reasoning  !!}</blockquote>
            </flux:tooltip.content>
        </flux:tooltip>
    @endif
</div>
