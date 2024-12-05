@php 
  $field = $name ?? $attributes->whereStartsWith('wire:model')->first();
@endphp
<div class="form-item">
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
    <label class="option-label" for="{{$field}}">{{$label}}</label>
    @if (!empty($description))
    <div class="description" id="{{$field}}_desc">{{$description}}</div>
    @endif
</div>