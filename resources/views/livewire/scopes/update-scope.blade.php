<div>
    <h1>Edit Scope</h1>

    <form wire:submit="save">
        @include('fields')
        <x-forms.button.submit-group submitName="Update Scope" />
    </form>
</div>
