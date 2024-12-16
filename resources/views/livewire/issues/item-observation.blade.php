<x-forms.button
    size="xs"
    class="bg-wa-{{ Str::of($item->assessment->value())->lower()->replace('/', '') }}"
    textColor="text-black hover:text-white"
    title="{{ $item->assessment->value() }} guideline {{ $item->guideline->number }}: {{ $item->guideline->name }}"
    x-on:click.prevent="$dispatch('show-guideline', {number: {{ $item->guideline->number }} })"
>{{ $item->guideline->number }}</x-forms.button>
