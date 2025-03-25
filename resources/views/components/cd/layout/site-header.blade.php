<header id="site-header" aria-label="Site navigation">
    <nav class="dropdown-menu dropdown-menu-on-demand" id="main-navigation" aria-label="Main Navigation">
        <div class="container-fluid">
            <a id="mobile-home" href="#"><span class="sr-only">Home</span></a>
            <ul class="list-menu links [&_li]:border-r-0!">
                @can('view-any', \App\Models\Project::class)
                    <li><a href="{{ route('projects') }}">Projects</a></li>
                @endcan
                <li><a href="{{ route('guidelines.index') }}">Guidelines</a></li>
                <li><a href="{{ route('categories.index') }}">Categories</a></li>
                <li><a href="{{ route('criteria.index') }}">Criteria</a></li>
                <li><a href="{{ route('chat') }}">AI Chat</a></li>
                @can('view-any', \App\Models\Team::class)
                    <li><a href="{{ route('users.manage') }}">Teams</a></li>
                @endcan
            </ul>
        </div>
    </nav>
</header>
