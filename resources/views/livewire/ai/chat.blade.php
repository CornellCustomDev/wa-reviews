<div>
    <h2>AI Assisted WA Guidelines Chat</h2>

    <p>
        This AI chatbot is prompted to review web accessibility issue descriptions to identify applicable guidelines and recommendations based on the <a href="{{ route('guidelines.md') }}">Cornell Web Accessibility Testing Guidelines</a>.
    </p>
    <p>
        Enter a description of a web accessibility issue and the AI chatbot will identify applicable guidelines and recommendations.
    </p>

    @foreach ($messages as $message)
        <div class="mb-4">
            <hr>
            <h3 class="h5">{{ ucfirst($message['role']) }}</h3>
            <div @class(['p-2', 'rounded-sm', 'bg-cds-gray-200' => $message['role'] == 'assistant' ])>{!! Str::of($message['content'])->markdown() !!}</div>
        </div>
    @endforeach

    <hr>

    <form wire:submit="sendMessage">
        <h3>{{ count($messages) == 0 ? 'Web accessibility issue' : 'Your response' }}:</h3>
        <flux:textarea
            label="Chat message"
            wire:model="userMessage"
            placeholder="Type your message here..."
            rows="auto"
            class="mb-4!"
            wire:loading.attr="disabled"
            @keydown.enter="!$event.shiftKey && ($event.preventDefault(), $wire.sendMessage())"
        />
        <x-forms.button type="submit" wire:loading.attr="disabled">
            Send
        </x-forms.button>
    </form>

    <div x-show="false && $wire.response != null && $wire.response != ''" x-cloak>
        <hr>
        <div class="panel accent-gold fill">
            <h3>Debugging info</h3>
            <pre>{{ print_r($messages, true) }}</pre>
        </div>
    </div>
</div>

{{--<x-slot:sidebarPrimary>--}}
{{--    <div x-data="{ prompt: false }">--}}
{{--        <button @click="prompt = !prompt" style="float:right">--}}
{{--            <span x-text="prompt ? 'Hide' : 'Show'"></span> Prompt--}}
{{--        </button>--}}
{{--        <div x-show="prompt">--}}
{{--            <h3>Prompt</h3>--}}
{{--            <p class="smallprint">This is the information given to the AI at the start of the chat telling it how to respond. It has been tuned for the WA review process and the Cornell guidelines document. It is a work in progress!</p>--}}
{{--            <div class="message-text">{!! Str::of($prompt)->markdown() !!}</div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</x-slot>--}}
