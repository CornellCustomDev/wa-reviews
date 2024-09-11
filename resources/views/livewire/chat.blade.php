<div>
    <div>
        <h3>Prompt</h3>
        <div class="message-text">{!! nl2br($prompt) !!}</div>
    </div>
    @foreach ($messages as $message)
        <div>
            <h3>{{ $message['role'] }}:</h3>
            <div class="message-text">{!! \Str::of($message['content'])->markdown() !!}</div>
        </div>
    @endforeach

    <hr>

    {{--  The form to send a message --}}
    <form wire:submit="sendMessage">
        <h3>Your message:</h3>
        <textarea wire:model="userMessage" placeholder="Type your message here..."></textarea>
        <button type="submit">Send</button>
    </form>
</div>
