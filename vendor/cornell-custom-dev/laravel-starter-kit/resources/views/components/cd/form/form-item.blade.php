@php
    $is_required = $required ?? 0;
@endphp
<div class="form-item @if($is_required) required @endif {{ $classes ?? '' }}">
    <div class="form-field">
        <label
            for="{{ $field }}"
            @if($is_required) aria-required="true" @endif
        >
            @if ($is_required) <span style="color:red";>* </span> @endif {!! $field_title !!}:
        </label>

        {{ $slot }}
    @if (!empty($description))
        <div class="description" id="{{$field}}_desc">{!!$description!!}</div>   
    @endif
    </div>

    @error($field)
    <div class="error" role="alert">
        {!! $message !!}
    </div>
    @enderror
</div>
