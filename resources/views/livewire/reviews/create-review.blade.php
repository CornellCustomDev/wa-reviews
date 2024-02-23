<div>
    <h1>{{ $this->project->name }}: Create Review</h1>

    <form wire:submit="save">
        <x-cd.form.text label="Target" wire:model="form.target" />
        <x-cd.form.text label="Description" wire:model="form.description" />
        <x-cd.form.text label="Recommendation" wire:model="form.recommendation" />

        <input type="submit" value="Create Review">
        <a href="{{ route('reviews.index', $this->project) }}" >
            <input type="button" value="Cancel">
        </a>
    </form>
</div>
