<div>
    <div class="cwd-component">
        <h2>AI Help</h2>
        <button type="button" wire:click="populateGuidelines">
            Populate Guidelines
        </button>
        <span wire:loading> Analyzing...</span>
    </div>
    @if (!empty($response))
    <hr>
    <div class="cwd-component">
        <h3 class="h5">Response</h3>
        <pre>{{ $response }}</pre>
    </div>
    @endif
</div>
