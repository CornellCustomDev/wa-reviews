<div x-on:click="await $wire.edit(); $nextTick(() => { $refs.form_{{ $field }}.focus() })" wire:click.outside="close"
     style="cursor:pointer; min-height:1em">
    <div x-show="!$wire.editing">
        {{ $reviewItem->$field }}
    </div>
    <div x-show="$wire.editing">
        <form wire:submit="save">
            <label for="field-{{ $field }}" class="sr-only">{{ $label }}</label>
            @switch($fieldType)
                @case('select')
                    <x-forms.review-items.select :field="$field" :options="$form->getOptions($field)" />
                    @break
                @default
                    <x-forms.review-items.text :field="$field" />
            @endswitch
            @error("form.$field")
            <div>
                <span class="error">{{ $message }}</span>
            </div>
            @enderror
            <span wire:loading><span class="fas fa-sync fa-spin"></span><span class="sr-only">Saving...</span></span>
        </form>
    </div>
</div>
