<div>
    <h1>AI Page Analyzer</h1>

    <form wire:submit.prevent="analyze">
        <div>
            <label for="pageUrl">Page URL:</label>
            <input type="text" id="pageUrl" wire:model="pageUrl">
        </div>
        <button type="submit">Analyze</button>
    </form>

    @if ($pageUrl)
        <hr>

        <h2>Applicable Rules</h2>
        <ul>
            @foreach ($rules as $rule)
                <li><a href="{{ route('rules.show', $rule->id) }}">{{ $rule->name }}</a></li>
            @endforeach
        </ul>

        <h2>Page</h2>
        <div class="panel accent-blue-green">
            {!! $pageContent !!}
        </div>
    @endif
</div>
