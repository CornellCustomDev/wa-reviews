<div>
{{--  List the messages, with role=system messages on the left and role=user on the right --}}
    @foreach ($messages as $message)
        <div>
            <h3>{{ $message['role'] }}:</h3>
            <div class="message-text">{!! \Str::of($message['content'])->markdown() !!}</div>
        </div>
    @endforeach

    <hr>

    {{--  The form to send a message --}}
    <form wire:submit="sendMessage">
        <textarea wire:model="userMessage" placeholder="Type your message here..."></textarea>
        <button type="submit">Send</button>
    </form>
</div>
