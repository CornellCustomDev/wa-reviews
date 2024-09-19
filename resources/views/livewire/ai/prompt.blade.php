<div>
    <h1>AI Prompt Playground</h1>

    <form wire:submit="sendMessage">
        <label for="prompt">Prompt</label>
        <textarea wire:model="prompt" rows="10"></textarea>
        <label for="includeGuidelines">
            <input type="checkbox" wire:model="includeGuidelines"> Include <a href="{{ route('guidelines.md') }}">guidelines.md</a> document
        </label>

        @foreach ($messages as $message)
            <div>
                <hr>
                <h3 class="h4">{{ ucfirst($message['role']) }}</h3>
                <div class="message-text">{!! Str::of($message['content'])->markdown() !!}</div>
            </div>
        @endforeach

        <hr>

        <label for="userMessage">User Message</label>
        <textarea wire:model="userMessage" placeholder="Type your message here..."></textarea>
        <button type="submit">Send</button>
    </form>
</div>
