<div>
    <h1>Edit Issue</h1>

    <form wire:submit="save">
        <x-cd.form.text label="Target" wire:model="form.target"/>
        <x-cd.form.text label="Description" wire:model="form.description"/>

        <input type="submit" value="Update Issue">
        <a href="{{ route('scopes.show', [$form->issue->scope->project, $form->issue->scope]) }}">
            <input type="button" value="Cancel">
        </a>
    </form>
</div>
