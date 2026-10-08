@blaze(fold: true, safe: ['field', 'labelField', 'innerRadius', 'radius'])

@props([
    'field' => 'value',
    'labelField' => null,
    'innerRadius' => null,
    'radius' => null,
])

<template name="pie" field="{{ $field }}" {{ $attributes->only([])->merge([
        'label-field' => $labelField,
        'inner-radius' => $innerRadius,
        'radius' => $radius,
    ]) }}>
    <path {{ $attributes->class('[:where(&)]:stroke-white dark:[:where(&)]:stroke-zinc-900')->merge([
        'stroke-width' => '3',
        'stroke-linejoin' => 'round',
    ]) }}></path>
</template>
