<div>
    <div>
        <h2>AI Help</h2>
        @if ($useGuidelines)
            @can('update', $issue)
                <x-forms.button wire:click="populateGuidelines" icon="check">
                    Populate Guidelines
                </x-forms.button>
            @endcan
        @endif
        <x-forms.button.chat wire:click="$toggle('showChat')" :$showChat />
        <span wire:loading.delay wire:target="populateGuidelines"> Analyzing...</span>
    </div>

    <div x-show="$wire.showChat" x-cloak>
        <hr>
        <h3>AI Assistance Chat</h3>

        <p>
            This chatbot can answer questions about applicable guidelines and recommendations.
            <button type="button" wire:click="clearChat">Clear Chat</button>
        </p>

        @foreach ($chatMessages as $message)
            <div>
                <hr>
                <h4 class="h4">{{ ucfirst($message['role']) }}</h4>
                <div class="message-text">{!! Str::of($message['content'])->markdown() !!}</div>
            </div>
        @endforeach

        <form wire:submit="sendChatMessage">
            <label for="userMessage">Chat:</label>
            <textarea wire:model="userMessage" placeholder="Type your message here..."></textarea>
            <x-forms.button type="submit">Send</x-forms.button>
            <span wire:loading.delay wire:target="sendChatMessage"> Analyzing...</span>
        </form>
    </div>

    @if(!$showChat)
        <div x-show="$wire.feedback != ''" x-cloak>
            <hr>
            <div class="panel">
                <h3 class="h5">AI Response</h3>
                {!! Str::of(htmlentities($feedback))->markdown() !!}
            </div>
        </div>
    @endif

    <div x-show="$wire.response != null && $wire.response != ''" x-cloak>
        <hr>
        <div class="panel accent-gold fill">
            <h3>Debugging info</h3>
            <pre>{{ $debug }}</pre>
        </div>
    </div>
</div>
