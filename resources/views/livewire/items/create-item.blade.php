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

    <div class="expander">
        <h4>Assessment</h4>
        <div>
            @foreach(\App\Enums\Assessment::cases() as $case)
                <div data-cds-field-display class="mb-1.5!">
                    <flux:heading>
                        {{ $case->getDescription() }}
                    </flux:heading>
                    <flux:text>
                        {{ $case->getLongDescription() }}
                    </flux:text>
                </div>
            @endforeach
        </div>

        <h4>Impact</h4>
        <div>
            @foreach(\App\Enums\Impact::cases() as $case)
                <div data-cds-field-display class="mb-1.5!">
                    <flux:heading>
                        {{ $case->value() }}
                    </flux:heading>
                    <flux:text>
                        {{ $case->getLongDescription() }}
                    </flux:text>
                </div>
            @endforeach
        </div>
    </div>

    <flux:separator class="mb-4 clear-both" />

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
