<div>
    <h1>Edit Issue</h1>

    <form wire:submit="save">
        <x-cd.form.text label="Target" wire:model="form.target"/>
        <x-cd.form.text label="Description" wire:model="form.description"/>

        <x-forms.button type="submit">Update Issue</x-forms.button>
        <x-forms.button :href="route('scopes.show', [$form->issue->scope->project, $form->issue->scope])">Cancel</x-forms.button>
    </form>
</div>
