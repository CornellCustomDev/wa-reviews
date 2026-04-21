<div x-data="{ showChat: false }">
    <h2>Scope Actions</h2>

    <div class="mb-4">
        @can('update', $scope)
            <x-forms.button wire:click="clickContent()" icon="document" @class(['secondary' => $showContent])>
                <span><span x-show="$wire.showContent">Hide </span>Content</span>
            </x-forms.button>
        @endcan
        <x-forms.button wire:click="clickChat()" icon="chat-bubble-left-right" @class(['secondary' => $showChat])>
            <span><span x-show="$wire.showChat">Hide </span>Chat</span>
        </x-forms.button>
        @can('update', $scope)
            <x-forms.button wire:click="clickAnalyzer()" icon="sparkles" @class(['secondary' => $showAnalyzer])>
                <span x-show="$wire.showAnalyzer">Hide </span> Analyzer
            </x-forms.button>
        @endcan
        <x-forms.button wire:click="clickComments()" icon="chat-bubble-oval-left" @class(['secondary' => $showComments])>
            <span x-show="$wire.showComments">Hide </span> Comments
            @if($this->commentsCount > 0)
                <flux:badge size="sm" color="blue">{{ $this->commentsCount }}</flux:badge>
            @endif
        </x-forms.button>
    </div>

    <div x-show="$wire.showContent" x-cloak>
        <hr>
        <livewire:scopes.scope-page-content :$scope />
    </div>

    <div x-show="$wire.showChat" x-cloak>
        <hr>
        <livewire:scopes.scope-chat :$scope />
    </div>

    <div x-show="$wire.showAnalyzer" x-cloak>
        <hr>
        <livewire:scopes.scope-analyzer :$scope />
    </div>

    <div x-show="$wire.showComments" x-cloak>
        <hr>
        <livewire:comments.comments :commentable="$scope" />
    </div>
</div>
