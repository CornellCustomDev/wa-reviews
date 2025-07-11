<div>

    <div x-data="{ edit: $wire.entangle('showEdit').live }">
        @can('update', $document)
            <x-forms.button icon="pencil-square" class="float-right" x-show="!edit" x-on:click="edit = !edit" title="Edit document" />
            <x-forms.button icon="x-mark" x-cloak class="float-right secondary" x-show="edit" x-on:click="edit = !edit" title="Cancel editing project" />
        @endcan

        <div x-show="!edit">
            @if($form->title)
                <h1>{{ $form->title }}</h1>
            @endif
            <div>
                {!! $form->content !!}
            </div>
        </div>

        <div x-show="edit" x-cloak>
            <form wire:submit="save">
                <x-forms.input label="Title" wire:model="form.title" />

                <x-forms.textarea label="Content" wire:model="form.content" size="lg"/>

                <x-forms.button.submit-group submitName="Update Document" />
            </form>
        </div>
    </div>

</div>
