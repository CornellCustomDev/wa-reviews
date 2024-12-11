<div>
    <form wire:submit="save">
        <x-cd.form.text label="Target" wire:model="form.target"/>
        <x-cd.form.text label="Description" wire:model="form.description"/>

        <x-forms.button.submit-group submitName="Update Issue" />
    </form>
</div>
