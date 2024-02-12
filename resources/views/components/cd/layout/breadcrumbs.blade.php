@props(['breadcrumbs' => []])
<div id="breadcrumb-navigation" {{ $attributes }}>
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <ul class="list-menu">
            <li><a href="/"><span class="limiter">Home</span></a></li>
            @foreach($breadcrumbs as $breadcrumb => $route)
                @if ($route == 'active')
                    <li><span class="limiter">{{ $breadcrumb }}</span></li>
                @else
                    <li><a href="{{ $route }}"><span class="limiter">{{ $breadcrumb }}</span></a></li>
                @endif
            @endforeach
        </ul>
    </nav>
</div>
