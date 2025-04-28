<div
    x-data
    x-on:messages-updated.window="$refs.chatContainer.scrollTop = $refs.chatContainer.scrollHeight"
>
    @if($messages)
        <x-forms.button wire:click="clearChat()" icon="pencil-square" class="secondary float-right ml-2" size="xs" title="New Chat" />
    @endif
    <div class="mb-3">
        This AI chatbot can answer questions and provide recommendations for this accessibility issue and related guidelines.
    </div>

    <div class="max-h-96 overflow-y-auto mb-4 space-y-2"
         x-ref="chatContainer"
         x-transition.duration.500ms
{{--         wire:stream="messages"--}}
    >

        @foreach ($messages as $message)
            @if(isset($message['tool_calls']))
                @foreach($message['tool_calls'] as $tool_call)
                    <flux:card size="sm" class="max-w-[90%] overflow-x-auto bg-cds-blue-100!">
                        <pre class="mb-0">Using tool: {{ $this->getToolCallTool($tool_call)->getName() }}</pre>
                    </flux:card>
                @endforeach
            @else
                @switch($message['role'])
                    @case('user')
                        <div class="w-full flex justify-end">
                            <flux:card size="sm" class="max-w-[90%]">
                                {!! Str::of($message['content'])->markdown() !!}
                            </flux:card>
                        </div>
                        @break
                    @case('tool')
                        @break
                    @default
                        <flux:card size="sm" class="max-w-[90%] bg-cds-blue-50!">
                            {!! Str::of($message['content'])->markdown() !!}
                        </flux:card>
                        @break
                @endswitch
            @endempty
        @endforeach

    </div>

    <form x-on:submit.prevent="$wire.sendUserMessage().then(() => $dispatch('messages-updated'))" class="mt-4">
        <x-forms.textarea wire:model="userMessage" size="sm" label="Chat" :placeholder="$messages ? '' : 'Ask AI about this issue ...'" x-data @focusin="$dispatch('messages-updated')" />
        <x-forms.button type="submit">Send</x-forms.button>
        <span wire:loading.delay wire:target="sendUserMessage"> Analyzing...</span>
    </form>
</div>
