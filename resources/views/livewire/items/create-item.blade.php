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
    <livewire:ai.guideline-help :$issue :use-guidelines="false" />
</x-slot:sidebarPrimary>
