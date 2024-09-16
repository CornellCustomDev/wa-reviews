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
                {{ $issue->description }}
            </td>
        </tr>
    </table>

    <form wire:submit="save">
        @include('livewire.items.fields')

        <input type="submit" value="Save Observation">
        <a href="{{ route('issues.show', [$issue->project, $issue]) }}" >
            <input type="button" value="Cancel">
        </a>
    </form>

</div>

<x-slot:sidebarPrimary>
    <livewire:ai.guideline-help :$issue />
</x-slot:sidebarPrimary>
