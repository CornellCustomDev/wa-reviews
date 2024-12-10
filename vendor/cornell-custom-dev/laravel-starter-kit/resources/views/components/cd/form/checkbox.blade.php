<x-cd.form.form-item field="{{ $name ?? $attributes->whereStartsWith('wire:model')->first() }}" 
        classes="{{ $classes ?? '' }}" 
        :required="false"
        :description="$description??''"
>
@php 
  $field = $name ?? $attributes->whereStartsWith('wire:model')->first();
@endphp
    <x-slot name="field_title">{!! $label !!}</x-slot>
    <input type="checkbox" id="{{ $id ?? $field }}" 
        name="{{ $field }}"
        value="{{ $value }}" 
        {{ $attributes->whereStartsWith('wire') }}
        {{ $attributes->whereStartsWith('aria')  }}
        {{ $attributes->whereStartsWith('checked')  }}
        @if (!empty($description))
            aria-describedby="{{ $field }}_desc"
        @endif
    />
    <span class="option-label" for="{{$field}}">{!!$text??''!!}</span>
</x-cd.form.form-item>
