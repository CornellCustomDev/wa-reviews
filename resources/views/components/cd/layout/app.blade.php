@props([
    'siteTitle' => config('app.name'),
    'title',
    'subtitle' => null,
    'sidebar' => true,
    'supplementary' => false,
    'breadcrumbs' => [],
])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite('resources/css/app.css')

    <title>{{ $title }}</title>

    <link rel="icon" href="{{ asset('cwd-framework/favicon.ico') }}" type="image/vnd.microsoft.icon"/>

    <link href="{{ asset('cwd-framework/css/base.css') }}" rel="stylesheet">
    <link href="{{ asset('cwd-framework/css/cornell.css') }}" rel="stylesheet">
    <link href="{{ asset('cwd-framework/css/cwd_utilities.css') }}" rel="stylesheet">

    <!-- Activate for Cornell.edu typography and basic patterns -->
    <!-- <link rel="stylesheet" href="https://use.typekit.net/nwp2wku.css"> -->
    <!-- <link href="{{ asset('cwd-framework/css/cwd_patterns.css') }}" rel="stylesheet"> -->

    <!-- Icon Fonts -->
    <link href="{{ asset('cwd-framework/fonts/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('cwd-framework/fonts/material-design-iconic-font.min.css') }}" rel="stylesheet">

    <!-- Inter font -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

    @livewireStyles
    @fluxStyles
</head>
<body @class([
    'cu-seal',
    'sidebar' => $sidebar,
    'sidebar-right' => $sidebar,
    'sidebar-tint' => $sidebar,
    'sidebar-tint-edge' => $sidebar,
    'light',
])>

<div id="skipnav"><a href="#main">Skip to main content</a></div>

<div class="band" id="super-header">
    <x-cd.layout.cu-header :title="$siteTitle" :subtitle="$subtitle ?? ''"/>
    <x-cd.layout.site-header/>
</div>

<div id="main-content" class="band">
    <main id="main" class="container-fluid aria-target" tabindex="-1">
        <div class="row">
            @if ($sidebar && ($sidebarPrimary ?? false))
                <x-cd.layout.sidebar-top>
                    {{ $sidebarPrimary }}
                </x-cd.layout.sidebar-top>
            @endif
            <x-cd.layout.main-article :breadcrumbs="$breadcrumbs">
                {{ $slot }}
            </x-cd.layout.main-article>
            @if ($sidebar && ($sidebarSecondary ?? false))
                <x-cd.layout.sidebar-bottom>
                    {{ $sidebarSecondary }}
                </x-cd.layout.sidebar-bottom>
            @endif
        </div>
    </main>
</div>

@if ($supplementary)
    <x-cd.layout.supplementary-content>
        {{ $supplementary }}
    </x-cd.layout.supplementary-content>
@endif

<x-cd.layout.footer/>

<!-- jQuery and Contributed Components -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

<!-- CWD Components -->
<script src="{{ asset('cwd-framework/js/cwd.js') }}"></script>
<script src="{{ asset('cwd-framework/js/cwd_utilities.js') }}"></script>
<script src="{{ asset('cwd-framework/js/cwd_experimental.js') }}"></script>
<script>
    autoTOC();
</script>

@livewireScripts
@fluxScripts
</body>
</html>
