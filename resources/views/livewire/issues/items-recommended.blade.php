@props([
    'items' => [],
    // Issue or Scope
    'model' => null,
])
<div class="mt-4">
    <h3 class="h5">Suggested Guidelines</h3>
    @foreach($items as $item)
        <div class="mb-4">
            <flux:card size="sm">
                <h4 class="h5 mb-2">{{ $item->guideline->name }}</h4>

                <x-forms.field-display label="WCAG" variation="inline" class="mb-2!">
                    {{ $item->guideline->criterion->getLongName() }}
                </x-forms.field-display>

                <div class="mb-4">
                    @can('update', $model)
                        <div class="float-right">
                            <x-forms.button
                                    title="Accept AI recommendation for guideline {{ $item->guideline->number }}"
                                    icon="hand-thumb-up" size="xs"
                                    wire:click="confirmAI('{{ $item->guideline->number }}')"
                            />
                            <x-forms.button
                                    title="Reject AI recommendation for guideline {{ $item->guideline->number }}"
                                    icon="hand-thumb-down" size="xs"
                                    wire:click="rejectAI('{{ $item->guideline->number }}')"
                            />
                        </div>
                    @endcan
                    <x-forms.button
                            data-cds-button-assessment
                            class="{{ Str::of($item->assessment->value())->lower()->replace('/', '') }}"
                            size="xs"
                            x-on:click.prevent="$dispatch('show-guideline', {number: {{ $item->guideline->number }} })"
                    >
                        {{ $item->guideline->number }}
                    </x-forms.button>
                    <flux:badge data-cds-impact class="{{ Str::of($item->impact->value())->lower() }}"
                                size="sm">
                        {{ $item->impact->value() }}
                    </flux:badge>
                    <flux:tooltip toggleable position="right" class="align-middle">
                        <flux:button icon="sparkles" size="xs" variant="ghost"
                                     class="text-cds-blue-600!"/>
                        <flux:tooltip.content class="max-w-[20rem] space-y-2">
                            <flux:subheading class="text-white">AI Reasoning</flux:subheading>
                            <blockquote class="mb-0 pl-2">{!! $item->ai_reasoning !!}</blockquote>
                        </flux:tooltip.content>
                    </flux:tooltip>
                </div>

                <x-forms.field-display label="Observation" variation="inline">
                    {{ $item->description }}
                </x-forms.field-display>
                <x-forms.field-display label="Recommendation" variation="inline" class="mb-0!">
                    {{ $item->recommendation }}
                </x-forms.field-display>
            </flux:card>
        </div>
    @endforeach
</div>
<livewire:issues.item-show-guideline/>
