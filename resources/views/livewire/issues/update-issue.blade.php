<div>
    <form wire:submit="save">
        <x-forms.input label="Target" wire:model="form.target"/>
        <x-forms.textarea label="Description" wire:model="form.description"/>

        <x-forms.button.submit-group submitName="Update Issue" />
    </form>
</div>
