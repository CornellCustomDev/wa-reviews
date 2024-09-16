<div>
    <h1>{{ $this->project->name }}: Create Issue</h1>

    <form wire:submit="save">
        <x-cd.form.text label="Target" wire:model="form.target" />
        <x-cd.form.text label="Description" wire:model="form.description" />

        <input type="submit" value="Add Issue">
        <a href="{{ route('reviews.index', $this->project) }}" >
            <input type="button" value="Cancel">
        </a>
    </form>
</div>
