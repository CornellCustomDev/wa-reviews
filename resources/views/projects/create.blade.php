<x-cd.layout.app title="WA Reviews" subtitle="Project">
    <h1>Create New Project</h1>

    <form method="POST" action="{{ route('projects.store') }}">
        @csrf
        <label for="name">Project Name</label>
        <input type="text" id="name" name="name" required>

        <label for="site_url">Site URL</label>
        <input type="text" id="site_url" name="site_url" required>

        <label for="description">Description</label>
        <textarea id="description" name="description" required></textarea>

        <input type="submit" value="Create Project">
    </form>

</x-cd.layout.app>
