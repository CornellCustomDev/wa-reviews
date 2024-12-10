@props(['breadcrumbs' => []])
<div id="breadcrumb-navigation" {{ $attributes }}>
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item :href="route('welcome')" class="text-xs" icon="home" />
            @foreach($breadcrumbs as $breadcrumb => $route)
                <flux:breadcrumbs.item :href="$route">{{ $breadcrumb }}</flux:breadcrumbs.item>
            @endforeach
        </flux:breadcrumbs>
    </nav>
</div>
