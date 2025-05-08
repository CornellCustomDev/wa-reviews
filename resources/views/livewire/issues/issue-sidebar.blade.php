<div>
    <h2>AI Assistance</h2>

    <div class="mb-4">
        <x-forms.button wire:click="clickChat()" icon="chat-bubble-left-right" @class(['secondary' => $showChat])>
            <span x-show="$wire.showChat">Hide </span> Chat
        </x-forms.button>
        @can('update', $issue)
            <x-forms.button wire:click="clickAnalyzer()" icon="check" @class(['secondary' => $showAnalyzer]) >
                <span x-show="$wire.showAnalyzer">Hide </span> Guidelines
            </x-forms.button>
        @endcan
{{--        <x-forms.button wire:click="clickDebug()" icon="magnifying-glass" @class(['secondary' => $showDebug]) >--}}
{{--            <span x-show="$wire.showDebug">Hide </span> Debug--}}
{{--        </x-forms.button>--}}
    </div>

    <div x-show="$wire.showChat" x-cloak>
        <hr>
        <livewire:issues.issue-chat-new :$issue />
    </div>

    <div x-show="$wire.showAnalyzer" x-cloak>
        <hr>
        <livewire:issues.issue-analyzer :$issue />
    </div>

    <div x-show="$wire.showDebug" x-cloak>
        <hr>
        Debug
    </div>
</div>
