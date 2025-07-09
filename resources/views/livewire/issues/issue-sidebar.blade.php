<div>
    <h2>AI Assistance</h2>

    <div class="mb-4">
        <x-forms.button wire:click="clickChat()" icon="chat-bubble-left-right" @class(['secondary' => $showChat])>
            <span x-show="$wire.showChat">Hide </span> Chat
        </x-forms.button>
        @can('update', $issue)
            <x-forms.button wire:click="clickAnalyzer()" icon="sparkles" @class(['secondary' => $showAnalyzer]) >
                <span x-show="$wire.showAnalyzer">Hide </span> Analyze
            </x-forms.button>
        @endcan
    </div>

    <div x-show="$wire.showChat" x-cloak>
        <hr>
        <livewire:issues.issue-chat :$issue />
    </div>

    <div x-show="$wire.showAnalyzer" x-cloak>
        <hr>
        <livewire:issues.issue-analyzer :$issue />
    </div>
</div>
