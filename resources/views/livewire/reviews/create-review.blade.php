<div>
    <h1>{{ $this->project->name }}: Create Review</h1>

    <form wire:submit="save">
        <label for="target">Target</label>
        <input type="text" id="target" name="target" required wire:model="form.target">
        @error('form.target')
            <div>
                <span class="error">{{ $message }}</span>
            </div>
        @enderror

        <label for="description">Description</label>
        <textarea id="description" name="description" wire:model="form.description"></textarea>
        @error('form.description')
            <div>
                <span class="error">{{ $message }}</span>
            </div>
        @enderror

        <label for="recommendation">Recommendation</label>
        <textarea id="recommendation" name="recommendation" wire:model="form.recommendation"></textarea>
        @error('form.recommendation')
            <div>
                <span class="error">{{ $message }}</span>
            </div>
        @enderror

        <input type="submit" value="Create Review">
        <a href="{{ route('reviews.index', $this->project) }}" >
            <input type="button" value="Cancel">
        </a>
    </form>
</div>
