<x-cd.form.form-item field="{{ $name ?? $attributes->whereStartsWith('wire:model')->first() }}" 
        classes="{{ $classes ?? '' }}" 
        required="{{ (($required??'') === 'false') ? 0 : (boolval($required??'')?1:0) }}"
        description="{{ $description ?? ''}}"

>
    <x-slot name="field_title">{{ $label }}</x-slot>
    <div class="form-item">
        <input type="checkbox" id="{{ $id ?? $name ?? $attributes->whereStartsWith('wire:model')->first() }}" 
            name="{{ $name ?? $attributes->whereStartsWith('wire:model')->first() }}"
            value="{{ $value }}" 
            {{ $attributes->whereStartsWith('wire:model') }}
            {{ $attributes->whereStartsWIth('aria')  }}
            @if (!empty($description))
                aria-describedby="{{ $name ?? $attributes->whereStartsWith('wire:model')->first() }}_desc"
            @endif
        />
        <label class="option-label" for="{{$field}}">{{$label}}</label>
    </div>
</x-cd.form.form-item>
