<div>
    <h2>Guidelines AI Assistance</h2>

    <div class="flex items-center ml-2 mb-2 float-right">
        @if($this->chats->isNotEmpty())
            <flux:dropdown>
                <x-forms.button icon="chat-bubble-left" title="Select Chat" size="sm" />

                <x-forms.menu>
                    <x-forms.menu.item icon="pencil-square" wire:click="clearChat()">
                        New Chat
                    </x-forms.menu.item>
                    @if($selectedChat)
                        <x-forms.menu.item
                            icon="trash"
                            wire:click.prevent="deleteChat()"
                            wire:confirm="Are you sure you want to delete the chat '{{$selectedChat->name}}'?"
                        >
                            Delete Chat
                        </x-forms.menu.item>
                    @endif
                    <flux:menu.separator />
                    @foreach($chats as $chatHistory)
                        <x-forms.menu.item icon="chat-bubble-left" wire:click="selectChat({{ $chatHistory->id }})">
                            {{ $chatHistory->name }}
                        </x-forms.menu.item>
                    @endforeach
                </x-forms.menu>
            </flux:dropdown>
        @else
            <x-forms.button icon="pencil-square" wire:click="clearChat()" label="New Chat" />
        @endif
    </div>

    <div class="mb-3">
        This AI chatbot answers questions about accessibility guidelines.
    </div>
    <div class="mb-4 space-y-2" x-ref="chatContainer">
        @foreach ($this->chatMessages() as $message)
            @if(isset($message['tool_calls']))
                @foreach($message['tool_calls'] as $tool_call)
                    <flux:card size="sm" class="max-w-[90%] overflow-x-auto bg-cds-blue-100!">
                        <pre class="mb-0">Using tool: {{ $tool_call['function']['name'] }}</pre>
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

    <form wire:submit.prevent="sendUserMessage()" class="mt-4">
        <x-forms.textarea
            label="Chat"
            :placeholder="$messages ? '' : 'Ask AI about this guideline ...'"
            wire:model="userMessage"
            size="sm"
            toolbar="bold italic | link code ~ undo redo"
        />
        <x-forms.button type="submit">Send</x-forms.button>
        <span wire:loading.delay wire:target="sendUserMessage"> Analyzing...</span>
    </form>
</div>
