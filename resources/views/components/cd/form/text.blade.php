<x-cd.form.form-item field="{{ $name ?? $attributes->whereStartsWith('wire:model')->first() }}" 
        classes="{{ $classes ?? '' }}" 
        required="{{ (($required??'') === 'false') ? 0 : (boolval($required??'')?1:0) }}"
        :description="$description ?? ''"
>
@php 
  $field=$name ?? $attributes->whereStartsWith('wire:model')->first();
@endphp
    <x-slot name="field_title">{!! $label !!}</x-slot>
    <input  type="{{$type??'text'}}" 
            id="{{ $id ?? $field }}" 
            name="{{ $field }}"
        {{-- type: can be text, number, date, datetime, datetime-local, month, week, time, range, color --}}
        class="{{$class??''}}"   
        {{$attributes->only("min")}}
        {{$attributes->only("max")}}
        {{$attributes->only("step")}}
        {{$attributes->only("size")}}
        {{$attributes->only("placeholder")}}
        {{$attributes->whereStartsWIth('wire:model')}}
        {{$attributes->whereStartsWIth('aria')}}
        @if (!empty($description))
          aria-describedby="{{ $field }}_desc"
        @endif
    />
</x-cd.form.form-item>
