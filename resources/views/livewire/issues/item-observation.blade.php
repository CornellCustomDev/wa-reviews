<div>
    <flux:tooltip position="bottom" class="align-middle">
        <x-forms.button
            size="xs"
            class="bg-wa-{{ Str::of($item->assessment->value())->lower()->replace('/', '') }}"
            textColor="text-black hover:text-white"
            x-on:click.prevent="$dispatch('show-guideline', {number: {{ $item->guideline->number }} })"
        >{{ $item->guideline->number }}</x-forms.button>
        <flux:tooltip.content class="max-w-[600px]">
            <p>{{ $item->assessment->value() }} guideline {{ $item->guideline->number }}: {{ $item->guideline->name }}</p>
        </flux:tooltip.content>
    </flux:tooltip>

    @if($item->content_issue)
        <flux:tooltip toggleable position="right" class="align-middle">
            <flux:button icon="information-circle" size="sm" variant="ghost" />

            <flux:tooltip.content class="max-w-[20rem] space-y-2">
                <p>Content entry issue</p>
            </flux:tooltip.content>
        </flux:tooltip>
    @endif
</div>
