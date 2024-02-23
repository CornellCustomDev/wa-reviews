@php 
  $field = $name ?? $attributes->whereStartsWith('wire:model')->first();
@endphp
<div class="form-item">
    <input type="checkbox" id="{{ $id ?? $field }}" 
        name="{{ $field }}"
        value="{{ $value }}" 
        {{ $attributes->whereStartsWith('wire:model') }}
        {{ $attributes->whereStartsWith('aria')  }}
        {{ $attributes->whereStartsWith('checked')  }}
        @if (!empty($description))
            aria-describedby="{{ $field }}_desc"
        @endif
    />
    <label class="option-label" id="{{$field}}_desc">{{$label}}</label>
    @if (!empty($description))
    <div class="description">{{$description}}</div>
    @endif
</div>