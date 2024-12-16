@props([
    'label',
    'values'
])
<flux:radio.group :$label :$attributes size="sm" variant="horizontal">
    @foreach ($values as $option)
        <flux:radio :value="$option['value']" :label="$option['label']" />
    @endforeach
</flux:radio.group>
