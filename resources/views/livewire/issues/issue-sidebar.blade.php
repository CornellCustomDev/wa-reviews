<div>
    <h2>Issue Actions</h2>

    <div class="mb-4">
        <x-forms.button wire:click="clickChat()" icon="chat-bubble-left-right" @class(['secondary' => $showChat])>
            <span x-show="$wire.showChat">Hide </span> Chat
        </x-forms.button>
        @can('update', $issue)
            <x-forms.button wire:click="clickAnalyzer()" icon="sparkles" @class(['secondary' => $showAnalyzer]) >
                <span x-show="$wire.showAnalyzer">Hide </span> Analyze
            </x-forms.button>
        @endcan
        <x-forms.button wire:click="clickComments()" icon="chat-bubble-oval-left" @class(['secondary' => $showComments])>
            <span x-show="$wire.showComments">Hide </span> Comments
            @if($this->commentsCount > 0)
                <x-forms.button.badge>{{ $this->commentsCount }}</x-forms.button.badge>
            @endif
        </x-forms.button>
    </div>

    <div x-show="$wire.showChat" x-cloak>
        <hr>
        <livewire:issues.issue-chat :$issue />
    </div>

    <div x-show="$wire.showAnalyzer" x-cloak>
        <hr>
        <livewire:issues.issue-analyzer :$issue />
    </div>

    <div x-show="$wire.showComments" x-cloak>
        <hr>
        <livewire:comments.comments :commentable="$issue" />
    </div>
</div>
