@props([
    'submitName' => 'Submit',
    'cancelName' => 'Cancel',
    'cancelRoute' => 'javascript:history.back()',
])
<div class="mt-8">
    @if ($slot->isEmpty())
        <x-forms.button type="submit" variant="cds">{{ $submitName }}</x-forms.button>
        <x-forms.button :href="$cancelRoute" variant="cds-secondary">{{ $cancelName }}</x-forms.button>
    @else
        {{ $slot }}
    @endif
</div>
