@props([
    'heading' => 'AI Assistance',
    'description' => 'This AI chatbot answers questions about accessibility guidelines and issues.',

    'chats' => null,
    'selectedChat' => null,
    'messages' => null,
    'streaming' => false,
    'streamedResponse' => null,
])
<div>
    <h2>
        {{ $heading }}
    </h2>

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
        {{ $description }}
    </div>

    @if($this->chatMessages()->isNotEmpty())
    <div
        class="relative mb-4"
        x-data="{ atTop: true, atBottom: false, scrollPos: 0, maxScroll: 0, scrollToBottom: $wire.entangle('scrollToBottom')  }"
        x-init="(() => {
            const el = $refs.chatContainer;
            const update = () => {
                atTop = el.scrollTop === 0;
                atBottom = el.scrollTop + el.clientHeight >= el.scrollHeight - 1;
                scrollPos = el.scrollTop;
                maxScroll = el.scrollHeight - el.clientHeight;
            };
            el.addEventListener('scroll', update);
            update();
        })()"
        x-effect="() => {
            if (scrollToBottom) {
                $nextTick(() => {
                    $refs.chatContainer.scrollTo({ top: $refs.chatContainer.scrollHeight, behavior: 'smooth' });
                    scrollToBottom = false;
                });
            }
        }"
    >
        <button
            x-show="!atTop"
            class="scroll-nav-button scroll-fade-top"
            :style="{ opacity: atTop ? 0 : Math.min(scrollPos/40, 1) }"
            @click="$refs.chatContainer.scrollTo({ top: 0, behavior: 'smooth' })"
        >
            <flux:icon.arrow-up-circle class="h-6 w-6 text-gray-500 bg-[#f7f7f7] rounded-full" variant="solid" />
        </button>
        <div class="max-h-[calc(100vh-400px)] min-h-48 overflow-y-auto space-y-2" data-cds-chat x-ref="chatContainer">
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
        <button
            x-show="!atBottom"
            class="scroll-nav-button bottom-0 scroll-fade-bottom"
            :style="{ opacity: atBottom ? 0 : Math.min(maxScroll - scrollPos/40, 1) }"
            @click="$refs.chatContainer.scrollTo({ top: $refs.chatContainer.scrollHeight, behavior: 'smooth' })"
        >
            <flux:icon.arrow-down-circle class="h-6 w-6 text-gray-500 bg-[#f7f7f7] rounded-full" variant="solid" />
        </button>
    </div>
    @endif

    @if($streaming)
        <div wire:stream="streamedResponse">{{ $streamedResponse }}</div>
    @endif

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
