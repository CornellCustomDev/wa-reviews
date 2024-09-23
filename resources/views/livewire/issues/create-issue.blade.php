<div>
    <h1>{{ $this->scope->title }}: Add Issue</h1>

    <form wire:submit="save">
        <x-cd.form.text label="Target" wire:model="form.target" />
        <x-forms.textarea label="Description" wire:model="form.description" />

        <input type="submit" value="Add Issue">
        <a href="{{ route('scope.show', $this->scope) }}">
            <input type="button" value="Cancel">
        </a>
    </form>
</div>
