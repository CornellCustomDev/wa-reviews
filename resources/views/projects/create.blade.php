<x-cd.layout.app title="WA Reviews" subtitle="Project">
    <h1>Create New Project</h1>

    <form method="POST" action="{{ route('projects.store') }}">
        @csrf
        <div class="form-group">
            <label for="name">Project Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="site">Site</label>
            <input type="text" class="form-control" id="site" name="site" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Create Project</button>
    </form>

</x-cd.layout.app>
