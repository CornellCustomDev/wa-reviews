<select id="field-{{ $field }}" name="field-{{ $field }}"
        wire:model="form.{{ $field }}" x-ref="form_{{ $field }}"
        wire:change="save" wire:keydown.escape="cancel"
        autocomplete="off"
        @error("form.$field")
        aria-invalid="true"
        aria-description="{{ $message }}"
        @enderror
>
    @foreach ($options as $opt)
        <option value="{{ $opt['value'] }}" @disabled($opt['disabled'] ?? false) @selected($opt["selected"] ?? false)>{{ $opt['option'] ?? $opt['label'] }}</option>
    @endforeach
</select>
