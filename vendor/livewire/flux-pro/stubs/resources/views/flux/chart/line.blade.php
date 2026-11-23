@blaze(fold: true, safe: ['field'], unsafe: ['curve'])

@props([
    'field' => 'value',
])

<template name="line" field="{{ $field }}" {{ $attributes->only(['curve']) }}>
    <path {{ $attributes->except('curve')->class('[:where(&)]:text-zinc-800')->merge([
        'stroke' => 'currentColor',
        'stroke-width' => '2',
        'fill' => 'none',
        'stroke-linecap' => 'round',
        'stroke-linejoin' => $attributes->get('curve') === 'step' ? 'miter' : 'round',
    ]) }}></path>
</template>
