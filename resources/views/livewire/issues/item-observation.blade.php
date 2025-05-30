<div class="inline-flex flex-nowrap items-center gap-x-0.5">
    <flux:tooltip position="bottom" class="align-middle">
        <x-forms.button
            data-cds-button-assessment
            class="{{ Str::of($item->assessment->value())->lower()->replace('/', '') }}"
            size="xs"
            x-on:click.prevent="$dispatch('show-guideline', {number: {{ $item->guideline->number }} })"
        >
            {{ $item->guideline->getNumber() }}
        </x-forms.button>

        <flux:tooltip.content class="max-w-[600px]">
            @if($item->guideline->number < 100)
                <p>{{ $item->assessment->getDescription() }} for Guideline {{ $item->guideline->number }}
                    : {{ $item->guideline->getCriterionInfo() }} - {{ $item->guideline->name }}</p>
            @else
                <p>{{ $item->assessment->getDescription() }} for Best
                    Practice: {{ $item->guideline->name }}</p>
            @endif
        </flux:tooltip.content>
    </flux:tooltip>

    @if($item->content_issue)
        <flux:tooltip toggleable position="right" class="align-middle">
            <flux:button icon="information-circle" size="sm" variant="ghost" class="text-cds-blue-600!"/>
            <flux:tooltip.content class="max-w-[20rem] space-y-2">
                <p>Content entry issue</p>
            </flux:tooltip.content>
        </flux:tooltip>
    @endif

    @if($item->impact)
        <flux:badge data-cds-impact class="{{ Str::of($item->impact->value())->lower() }}" size="sm">
            {{ $item->impact->value() }}
        </flux:badge>
    @endif

    @if($item->wasAiGenerated())
        <flux:tooltip toggleable position="right" class="align-middle">
            <flux:button icon="sparkles" size="xs" variant="ghost" class="text-cds-blue-600!"/>
            <flux:tooltip.content class="max-w-[20rem] space-y-2">
                <flux:subheading class="text-white">AI Reasoning</flux:subheading>
                <blockquote class="mb-0 pl-2">{!! $item->ai_reasoning  !!}</blockquote>
            </flux:tooltip.content>
        </flux:tooltip>
    @endif
</div>
