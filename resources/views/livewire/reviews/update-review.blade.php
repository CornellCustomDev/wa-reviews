<div>
    <h1>Edit Review</h1>

    <form wire:submit="save">
        <x-cd.form.text label="Target" wire:model="form.target" />
        <x-cd.form.text label="Description" wire:model="form.description" />
        <x-cd.form.text label="Recommendation" wire:model="form.recommendation" />

        <input type="submit" value="Update Review">
        <a href="{{ route('reviews.show', [$form->review->project, $form->review]) }}" >
            <input type="button" value="Cancel">
        </a>
    </form>
</div>
