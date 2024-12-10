<div>
    <div>
        <x-forms.button.chat wire:click="$toggle('showChat')" :$showChat />
    </div>

    <div x-show="$wire.showChat">
        <hr>
        <h3>AI Assistance Chat</h3>

        <p>
            This chatbot can answer questions for this scope.
            <x-forms.button size="xs" variant="cds-secondary" wire:click="clearChat">Clear Chat</x-forms.button>
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
        <div x-show="$wire.feedback != ''">
            <hr>
            <div class="panel">
                <h3 class="h5">AI Response</h3>
                {!! Str::of($feedback)->markdown() !!}
            </div>
        </div>
    @endif

    <div x-show="$wire.response != null && $wire.response != ''" class="hidden">
        <hr>
        <div class="panel accent-gold fill">
            <h3>Debugging info</h3>
            <pre>{{ $response }}</pre>
        </div>
    </div>
</div>
