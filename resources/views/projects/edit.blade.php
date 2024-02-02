<x-cd.layout.app title="WA Reviews" subtitle="Project">
    <h1>Edit Project</h1>

    <form method="POST" action="{{ route('projects.update', $project) }}">
        @csrf
        @method('PATCH')
        <label for="name">Project Name</label>
        <input type="text" id="name" name="name" value="{{ $project->name }}" required>

        <label for="site_url">Site URL</label>
        <input type="text" id="site_url" name="site_url" value="{{ $project->site_url }}" required>

        <label for="description">Description</label>
        <textarea id="description" name="description" required>{{ $project->description }}</textarea>

        <input type="submit" value="Update Project">
    </form>
</x-cd.layout.app>
