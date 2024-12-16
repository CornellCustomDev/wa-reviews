<div>
    <h2>Assistance</h2>

    <livewire:ai.scope-chat :$scope />

    <livewire:guidelines.sidebar />

    <div x-show="$wire.response != null && $wire.response != ''" x-cloak>
        <hr>
        <div class="panel accent-gold fill">
            <h3>Debugging info</h3>
            <pre>{{ $response }}</pre>
        </div>
    </div>
</div>
