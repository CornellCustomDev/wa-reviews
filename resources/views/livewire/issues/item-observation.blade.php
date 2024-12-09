<x-forms.button
    size="xs"
    class="bg-wa-{{ Str::lower($item->assessment->value()) }}"
    textColor="text-black hover:text-white"
    title="{{ $item->assessment->value() }}: View Guideline {{ $item->guideline->number }}"
    x-on:click.prevent="$dispatch('show-guideline', {number: {{ $item->guideline->number }} })"
>{{ $item->guideline->number }}</x-forms.button>
