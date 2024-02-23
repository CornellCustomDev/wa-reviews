<x-cd.form.form-item field="{{ $name ?? $attributes->whereStartsWith('wire:model')->first() }}" 
        classes="{{ $classes ?? '' }}" 
        required="{{ (($required??'') === 'false') ? 0 : (boolval($required??'')?1:0) }}"
        :description="$description ?? ''"
>
@php 
  $field=$name ?? $attributes->whereStartsWith('wire:model')->first();
@endphp
    <x-slot name="field_title">{!! $label !!}</x-slot>
    <select id="{{ $id ?? $field }}" 
            name="{{ $field }}"
        @if ($disabled??false)
            disabled
        @endif
        {{$attributes->only("multiple")}}
        {{$attributes->whereStartsWith("wire:model")}}
        {{$attributes->whereStartsWIth('aria')}}
        @if (!empty($description))
          aria-describedby="{{ $field }}_desc"
        @endif
    />
    @foreach ($options as $opt)
        <option value="{{$opt['value']}}" @if ($opt['disabled']??false) disabled="disabled" @endif >{{$opt['option']}}</option>
    @endforeach
    </select>
</x-cd.form.form-item>
