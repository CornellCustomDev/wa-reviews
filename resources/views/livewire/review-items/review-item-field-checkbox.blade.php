<div>
    <form wire:submit="save">
        <input type="checkbox"
               id="field-{{ $field }}" name="field-{{ $field }}"
               wire:model="form.{{ $field }}"
               wire:click="save"
        /><label for="field-{{ $field }}" class="sr-only">{{ $label }}</label>
        <span wire:loading><span class="fas fa-sync fa-spin"></span><span class="sr-only">Saving...</span></span>
    </form>
</div>
