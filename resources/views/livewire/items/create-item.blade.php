<div>
    <h1>Add Observation</h1>

    <table class="table bordered">
        <tr>
            <th>
                Target
            </th>
            <td>
                {{ $issue->target }}
            </td>
        </tr>
        <tr>
            <th>
                Description
            </th>
            <td>
                {!! $issue->description !!}
            </td>
        </tr>
    </table>

    <form wire:submit="save">
        @include('livewire.items.fields')

        <x-forms.button.submit-group submitName="Add Item" />
    </form>

</div>

<x-slot:sidebarPrimary>
    <div x-data="{ showChat: false }">
        <h2>AI Assistance</h2>

        <div class="mb-4">
            <x-forms.button x-on:click="showChat = ! showChat" icon="chat-bubble-left-right" x-bind:class="{ 'secondary': showChat }">
                <span><span x-show="showChat">Hide </span>Chat</span>
            </x-forms.button>
        </div>

        <div x-show="showChat" x-cloak>
            <hr>
            <livewire:items.item-chat :$issue />
        </div>
    </div>
</x-slot:sidebarPrimary>
