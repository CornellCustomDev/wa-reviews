@props(['breadcrumbs' => []])
<article id="main-article" class="primary">
    @if(count($breadcrumbs) > 0)
        <x-cd.layout.breadcrumbs :breadcrumbs="$breadcrumbs" />
    @endif
    {{ $slot }}
</article>
