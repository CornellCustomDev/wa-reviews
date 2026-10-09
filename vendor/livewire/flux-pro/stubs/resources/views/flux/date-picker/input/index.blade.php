@blaze(fold: true, unsafe: [
    // variant props
    'placeholder', 'clearable', 'dropdown', 'invalid', 'size',
])

@props([
    'variant' => 'native',
])

<flux:delegate-component :component="'date-picker.input.variants.' . $variant">{{ $slot }}</flux:delegate-component>
