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
    <h3>Instructions</h3>
    <p>
        An observation provides the assessment against a specific success criterion.
    </p>
    <x-forms.field-display label="Assessment">
        <ul>
            <li>
                <strong>Fail</strong>: The item does not meet the success criterion.
            </li>
            <li>
                <strong>Warn</strong>: No strict failures, but the user's experinece is negatively
                impacted or goes against best practices.
            </li>
            <li>
                <strong>Pass</strong>: The item meets the success criterion.
            </li>
            <li>
                <strong>N/A</strong>: The item is not applicable to the success criterion.
            </li>
        </ul>
    </x-forms.field-display>

    <div x-data="{ showChat: false }">
        <h3>AI Assistance</h3>

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
