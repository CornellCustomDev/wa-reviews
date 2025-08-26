@props(['breadcrumbs' => []])
<div id="breadcrumb-navigation" class="print:visually-hidden" {{ $attributes }}>
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <flux:breadcrumbs class="mb-4">
            <flux:breadcrumbs.item :href="route('welcome')" class="text-xs" icon="home" />
            @foreach($breadcrumbs as $breadcrumb => $route)
                <flux:breadcrumbs.item href="{{ $route !== 'active' ? $route : '' }}">{{ $breadcrumb }}</flux:breadcrumbs.item>
            @endforeach
        </flux:breadcrumbs>
    </nav>
</div>
