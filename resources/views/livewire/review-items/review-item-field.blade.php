<div x-on:click="await $wire.edit(); $nextTick(() => { $refs.form_{{ $field }}.focus() })" wire:click.outside="close" style="cursor:pointer; min-height:1em">
    <div x-show="!$wire.editing">
        {{ $reviewItem->$field }}
    </div>
    <div x-show="$wire.editing">
        <form wire:submit="save">
            <label for="field-{{ $field }}" class="sr-only">{{ $label }}</label>
            <input
                type="text" id="field-{{ $field }}" name="field-{{ $field }}"
                wire:model="form.{{ $field }}" x-ref="form_{{ $field }}"
                wire:keydown.return="save" wire:keydown.escape="cancel"
                {{--                            wire:keyup.tab="next({{ $reviewItem->id }})"--}}
                @class(['panel accent-red' => $errors->has("form.$field")])
                autocomplete="off"
                @error("form.$field")
                aria-invalid="true"
                aria-description="{{ $message }}"
                @enderror
            />
            @error("form.$field")
            <div>
                <span class="error">{{ $message }}</span>
            </div>
            @enderror
            {{--                        <span wire:loading>Saving...</span>--}}
        </form>
    </div>
</div>
