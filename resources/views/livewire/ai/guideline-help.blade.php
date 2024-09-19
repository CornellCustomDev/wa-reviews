<div>
    <div>
        <h2>AI Help</h2>
        <button type="button" wire:click="populateGuidelines">
            Populate Guidelines
        </button>
        <button type="button" wire:click="$toggle('showChat')">
            <span x-text="$wire.showChat ? 'Hide ' : ''"></span>Chat
        </button>
        <span wire:loading wire:target="populateGuidelines"> Analyzing...</span>
    </div>

    <div x-show="$wire.showChat">
        <hr>
        <h3>AI Assistance Chat</h3>

        <p>
            This chatbot can answer questions about applicable guidelines and recommendations.
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
            <button type="submit">Send</button>
            <span wire:loading wire:target="sendChatMessage"> Analyzing...</span>
        </form>
    </div>

    @if (!empty($response))
        <hr>
        <div class="panel accent-gold fill">
            <h3>Debugging info</h3>
            <h4 class="h5">AI Response</h4>
            <pre>{{ $response }}</pre>
        </div>
    @endif
</div>
