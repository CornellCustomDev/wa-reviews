<div>
    <h1>Create Project</h1>

    <form wire:submit="save">
        <x-cd.form.text label="Project Name" wire:model="form.name" />
        <x-cd.form.text label="Site URL" wire:model="form.site_url" />
        <x-cd.form.text label="Description" wire:model="form.description" />

        <input type="submit" value="Create Project">
        <a href="{{ route('projects') }}" >
            <input type="button" value="Cancel">
        </a>
    </form>
</div>
