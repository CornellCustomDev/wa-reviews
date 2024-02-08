<div>
    <h1>Edit Project</h1>
    <form wire:submit="save">
        <label for="name">Project Name</label>
        <input type="text" id="name" name="name" wire:model="form.name">
        @error('form.name')
            <div>
                <span class="error">{{ $message }}</span>
            </div>
        @enderror

        <label for="site_url">Site URL</label>
        <input type="text" id="site_url" name="site_url" required wire:model="form.site_url">
        @error('form.site_url')
            <div>
                <span class="error">{{ $message }}</span>
            </div>
        @enderror

        <label for="description">Description</label>
        <textarea id="description" name="description" wire:model="form.description"></textarea>

        <input type="submit" value="Update Project">

        <a href="{{ route('projects.show', $form->project) }}" >
            <input type="button" value="Cancel">
        </a>
    </form>
</div>
