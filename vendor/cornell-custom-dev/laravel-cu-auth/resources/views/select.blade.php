<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ config('cu-auth.idp_selection.page.heading') }} | {{ config('app.name') }}</title>
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
        }
        html {
            -webkit-text-size-adjust: 100%;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            background: #fff;
            color: #000;
            font-family: "freight-sans-pro", "Avenir Next", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 16px;
            line-height: 1.5;
            font-variant-numeric: lining-nums;
        }
        @media (min-width: 992px) {
            body {
                font-size: 18px;
            }
        }
        a {
            color: #1176ac;
        }
        a:hover,
        a:focus {
            color: #000;
        }
        a:focus-visible {
            outline: 2px solid #1176ac;
            outline-offset: 2px;
        }
        .cu-auth-container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 16px;
        }
        @media (min-width: 768px) {
            .cu-auth-container {
                padding: 0 30px;
            }
        }
        @media (min-width: 992px) {
            .cu-auth-container {
                padding: 0 60px;
            }
        }
        .cu-auth-skip {
            position: absolute;
            top: -100px;
            left: 16px;
            z-index: 10;
            padding: 0.5em 1em;
            background: #fff;
            color: #000;
        }
        .cu-auth-skip:focus {
            top: 8px;
        }

        .cu-auth-brand {
            background: #b31b1b;
        }
        .cu-auth-brand .cu-auth-container {
            display: flex;
            align-items: center;
            height: 48px;
        }
        .cu-auth-brand a {
            display: block;
            line-height: 0;
        }
        .cu-auth-brand a:focus-visible {
            outline-color: #fff;
        }
        .cu-auth-title {
            display: flow-root;
            background: #f7f7f7;
        }
        .cu-auth-title h1 {
            margin: 20px 0 16px -2px;
            font-size: 26px;
            font-weight: 400;
            line-height: 1.2;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        @media (min-width: 768px) {
            .cu-auth-title h1 {
                font-size: 34px;
            }
        }
        @media (min-width: 992px) {
            .cu-auth-title h1 {
                margin: 25px 0 21px -2px;
                font-size: 40px;
            }
        }
        @media (min-width: 1200px) {
            .cu-auth-title h1 {
                margin: 30px 0 26px -2px;
                font-size: 46px;
            }
        }

        .cu-auth-main {
            flex: 1;
            padding: 1.25em 0 2.5em;
        }
        @media (min-width: 768px) {
            .cu-auth-main {
                padding-top: 2em;
            }
        }

        .cu-auth-footer {
            padding: 20px 0;
            background: #292929;
            color: #bbb;
            font-size: 15px;
            line-height: 1.4;
        }
        .cu-auth-footer ul {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5em 1.5em;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .cu-auth-footer .cu-auth-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 0.5em 1.5em;
        }
        .cu-auth-footer a {
            color: #fff;
            text-decoration-color: rgba(255, 255, 255, 0.4);
        }
        .cu-auth-footer a:hover,
        .cu-auth-footer a:focus {
            color: #fff;
            text-decoration-color: #fff;
        }
        .cu-auth-footer a:focus-visible {
            outline-color: #fff;
        }
    </style>
</head>
<body>
<a class="cu-auth-skip" href="#main">Skip to main content</a>

<header>
    <div class="cu-auth-brand">
        <div class="cu-auth-container">
            <a href="https://www.cornell.edu">@include('cu-auth::partials.cornell-logo')</a>
        </div>
    </div>
    <div class="cu-auth-title">
        <div class="cu-auth-container">
            <h1>{{ config('cu-auth.idp_selection.page.heading') }}</h1>
        </div>
    </div>
</header>

<main id="main" class="cu-auth-main" tabindex="-1">
    <div class="cu-auth-container">
        <x-cu-auth::idp-selection heading="" :redirect-url="$redirectUrl"/>
    </div>
</main>

<footer class="cu-auth-footer">
    <div class="cu-auth-container">
        <ul>
            <li>&copy;{{ date('Y') }} <a href="https://www.cornell.edu">Cornell University</a></li>
            <li><a href="https://privacy.cornell.edu/">University Privacy</a></li>
        </ul>
        <ul>
            <li>
                <a href="https://accessibility.cornell.edu/information-technology/web-accessibility/web-accessibility-assistance/">Web Accessibility Assistance</a>
            </li>
        </ul>
    </div>
</footer>
</body>
</html>
