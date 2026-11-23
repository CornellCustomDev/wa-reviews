@once
<style>
    .cu-auth-idp-selection .cu-auth-idps {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .cu-auth-idp-selection .cu-auth-idp {
        margin: 0 0 1.5em;
        padding: 0;
    }
    .cu-auth-idp-selection a.cu-auth-idp-button,
    .cu-auth-idp-selection a.cu-auth-idp-button:visited {
        box-sizing: border-box;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5em;
        width: 100%;
        max-width: 370px;
        margin: 0.25em 0 0.4em;
        padding: 0.9em 1.2em;
        border-radius: 4px;
        background: #2d668e;
        color: #fff;
        font-family: "freight-text-pro", Georgia, "Times New Roman", serif;
        font-size: 19px;
        font-weight: 400;
        line-height: 1.1;
        text-decoration: none;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    @media (min-width: 834px) {
        .cu-auth-idp-selection a.cu-auth-idp-button {
            max-width: 420px;
            font-size: 24px;
        }
    }
    .cu-auth-idp-selection a.cu-auth-idp-button:hover,
    .cu-auth-idp-selection a.cu-auth-idp-button:focus {
        background: #2471a7;
        color: #fff;
    }
    .cu-auth-idp-selection a.cu-auth-idp-button:active {
        background: #185076;
    }
    .cu-auth-idp-selection a.cu-auth-idp-button:focus-visible {
        outline: 3px solid #1176ac;
        outline-offset: 2px;
    }
    .cu-auth-idp-selection a.cu-auth-idp-button::after {
        content: "";
        flex: none;
        width: 0.38em;
        height: 0.38em;
        margin-right: 0.15em;
        border-top: 2px solid currentColor;
        border-right: 2px solid currentColor;
        transform: rotate(45deg);
    }
    .cu-auth-idp-selection .cu-auth-idp-info {
        margin: 0;
        font-size: 0.875em;
    }
    .cu-auth-sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        margin: -1px;
        padding: 0;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
</style>
@endonce
<section {{ $attributes->class('cu-auth-idp-selection') }}>
    @if ($heading)
        <h2 class="cu-auth-idp-heading">{{ $heading }}</h2>
    @endif
    <ul class="cu-auth-idps">
        @foreach ($idps as $idp)
            <li class="cu-auth-idp">
                <a class="cu-auth-idp-button" href="{{ $idp['url'] }}">
                    <span><span class="cu-auth-sr-only">Sign in with </span>{{ $idp['label'] }}</span>
                </a>
                @if ($idp['info_url'])
                    <p class="cu-auth-idp-info">
                        <a href="{{ $idp['info_url'] }}" target="_blank" rel="noopener">{{ $idp['info_text'] ?? 'More information' }}<span class="cu-auth-sr-only"> (opens in a new tab)</span></a>
                    </p>
                @endif
            </li>
        @endforeach
    </ul>
</section>
