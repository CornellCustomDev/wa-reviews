<div>
    <h1>Update Item</h1>

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
                {{ $issue->description }}
            </td>
        </tr>
    </table>

    <form wire:submit="save">
        @include('livewire.items.fields')

        <x-forms.button type="submit">Save Observation</x-forms.button>
        <x-forms.button :href="route('issue.show', $issue)" variant="cds-secondary">Cancel</x-forms.button>
    </form>
</div>
