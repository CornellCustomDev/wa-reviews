@props(['route', 'title'])
<a href="{{ $route }}" title="{{ $title }}" {{ $attributes->class(['link-button', 'link']) }}>
    {{ trim($slot) ?: $title }}
</a>
