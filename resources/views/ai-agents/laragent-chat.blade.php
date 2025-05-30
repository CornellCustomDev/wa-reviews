@props([
    'description' => 'This AI chatbot answers questions about accessibility guidelines and issues.',
])
<div>
    <div class="flex items-center ml-2 mb-2 float-right">
        <flux:dropdown>
            <x-forms.button icon="chat-bubble-left" title="Select Chat" size="sm" />

            <x-forms.menu>
                <x-forms.menu.item icon="pencil-square" wire:click="newChat()">
                    New Chat
                </x-forms.menu.item>
                @if($this->chats()->get($selectedChatKey))
                    <x-forms.menu.item
                        icon="trash"
                        wire:click.prevent="deleteChat()"
                        wire:confirm="Are you sure you want to delete the chat '{{ $this->chats()->get($selectedChatKey)->name }}'?"
                    >
                        Delete Chat
                    </x-forms.menu.item>
                    <flux:menu.separator />
                @endif
                @foreach($this->chats() as $chatHistory)
                    <x-forms.menu.item icon="chat-bubble-left" wire:click="selectChat('{{ $chatHistory->ulid }}')">
                        {{ $chatHistory->name }}
                    </x-forms.menu.item>
                @endforeach
            </x-forms.menu>
        </flux:dropdown>
    </div>

    <div class="mb-3">
        {!! $description !!}
    </div>

    <div
        class="relative mb-4"
        x-data="{ atTop: true, atBottom: false, scrollPos: 0, maxScroll: 0, streaming: $wire.entangle('streaming')  }"
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

            window.addEventListener('scroll-to-bottom', () => {
                $nextTick(() => {
                    el.scrollTo({ top: $refs.chatContainer.scrollHeight, behavior: 'smooth' });
                    // Force a synthetic scroll event to trigger the update function
                    el.dispatchEvent(new Event('scroll'));
                });
            });

            window.addEventListener('scroll-to-response', () => {
                $nextTick(() => {
                    const el = $refs.latestResponse;
                    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    el.focus();
                    // Force a synthetic scroll event to trigger the update function
                    el.dispatchEvent(new Event('scroll'));
                });
            });

            window.addEventListener('focus-chat', () => {
                $nextTick(() => {
                    const el = $refs.userMessage;
                    el.focus();
                });
            });
        })()"
    >
        <button
            x-show="!atTop"
            x-cloak
            class="scroll-nav-button scroll-fade-top"
            :style="{ opacity: atTop ? 0 : Math.min(scrollPos/40, 1) }"
            @click="$refs.chatContainer.scrollTo({ top: 0, behavior: 'smooth' })"
        >
            <flux:icon.arrow-up-circle class="h-6 w-6 text-gray-500 bg-[#f7f7f7] rounded-full" variant="solid" />
        </button>
        <div
          @class([
            'max-h-[calc(100vh-400px)]',
            'min-h-48' => $this->chatMessages()->isNotEmpty(),
            'overflow-y-auto',
            'space-y-2',
          ])
          data-cds-chat
          x-ref="chatContainer"

        >
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
                            <div x-ref="latestResponse" tabindex="-1">
                                <flux:card size="sm" class="max-w-[90%] bg-cds-blue-50!">
                                    {!! Str::of($message['content'])->markdown() !!}
                                </flux:card>
                            </div>
                            @break
                    @endswitch
                @endempty
            @endforeach
            <template x-if="streaming">
                <div class="w-full flex justify-end">
                    <flux:card size="sm" class="max-w-[90%]">
                        {!! $userMessage !!}
                    </flux:card>
                </div>
            </template>
        </div>
        <button
            x-show="!atBottom"
            x-cloak
            class="scroll-nav-button bottom-0 scroll-fade-bottom"
            :style="{ opacity: atBottom ? 0 : Math.min(maxScroll - scrollPos/40, 1) }"
            @click="$refs.chatContainer.scrollTo({ top: $refs.chatContainer.scrollHeight, behavior: 'smooth' })"
        >
            <flux:icon.arrow-down-circle class="h-6 w-6 text-gray-500 bg-[#f7f7f7] rounded-full" variant="solid" />
        </button>
    </div>

    <div wire:stream="streamedResponse" wire:show="streaming" role="status" aria-live="polite" aria-atomic="false">{{ $streamedResponse }}</div>

    <div wire:show="showFeedback" wire:cloak>
        <flux:card size="sm" class="flex bg-cds-blue-200!">
            <div class="flex-1 min-w-0">
                <h3 class="h5">AI Response</h3>
                <div class="overflow-x-auto">
                    {!! Str::of(htmlentities($feedback))->markdown() !!}
                </div>
            </div>
            <div class="flex-none">
                <flux:button wire:click="$toggle('showFeedback')" variant="ghost" size="sm" icon="x-mark" inset="top right bottom" />
            </div>
        </flux:card>
    </div>

    <form wire:submit.prevent="sendUserMessage()" class="mt-4">
        <x-forms.textarea
            label="Chat"
            :placeholder="'Ask AI ...'"
            wire:model="userMessage"
            size="sm"
            toolbar="bold italic | link code ~ undo redo"
            x-ref="userMessage"
        />
        <x-forms.button type="submit">Send</x-forms.button>
        <span wire:loading.delay wire:target="sendUserMessage" role="status" aria-live="polite">Analyzing...</span>
    </form>
</div>
