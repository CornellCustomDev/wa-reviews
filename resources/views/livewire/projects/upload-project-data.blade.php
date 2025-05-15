<div>
    <flux:modal.trigger name="upload-data">
        <x-forms.button icon="arrow-up-tray" class="float-right mr-2" x-show="!edit" title="Upload Data" />
    </flux:modal.trigger>

    <flux:modal name="upload-data" class="md:w-96">
        <form wire:submit="save" class="mb-0!">
            <h3>Upload Data</h3>

            <p class="mb-6">Upload an existing Checklist xlsx file to import the Scope and Issues for this project.</p>

            <flux:input type="file" wire:model="checklist" size="sm" label="Checklist (.xlsx)" accept=".xlsx" />

            <div class="mt-8">
                <x-forms.button type="submit">Upload</x-forms.button>
            </div>
        </form>
    </flux:modal>
</div>
