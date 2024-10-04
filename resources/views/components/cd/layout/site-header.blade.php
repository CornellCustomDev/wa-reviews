<header id="site-header" aria-label="Site navigation">
    <nav class="dropdown-menu dropdown-menu-on-demand" id="main-navigation" aria-label="Main Navigation">
        <div class="container-fluid">
            <a id="mobile-home" href="#"><span class="sr-only">Home</span></a>
            <ul class="list-menu links">
                <li><a href="{{ route('projects') }}">Projects</a></li>
                <li><a href="{{ route('guidelines.index') }}">Guidelines</a></li>
                <li><a href="{{ route('criteria.index') }}">Criteria</a></li>
                <li><a href="{{ route('categories.index') }}">Categories</a></li>
                <li><a href="{{ route('act-rules.index') }}">ACT Rules</a></li>
                <li><a href="{{ route('chat') }}">Chat</a></li>
                <li><a href="{{ route('prompt') }}">Prompt</a></li>
                <li><a href="{{ route('analyze') }}">Analyze</a></li>
            </ul>
        </div>
    </nav>
</header>
