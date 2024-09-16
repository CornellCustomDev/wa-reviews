<input
    type="text" id="field-{{ $field }}" name="field-{{ $field }}"
    wire:model="form.{{ $field }}" x-ref="form_{{ $field }}"
    wire:keydown.return="save" wire:keydown.escape="cancel"
    {{-- wire:keyup.tab="next({{ $item->id }})"--}}
    @class(['panel accent-red' => $errors->has("form.$field")])
    autocomplete="off"
    @error("form.$field")
    aria-invalid="true"
    aria-description="{{ $message }}"
    @enderror
/>
