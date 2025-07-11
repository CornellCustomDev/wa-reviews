<div x-data="{ showEdit: false }" @close-edit.window="showEdit = false">
    @can('update', $model)
        <x-forms.button icon="pencil-square" class="float-right" x-show="!showEdit" x-on:click="showEdit = true" title="Edit" />
        <x-forms.button icon="x-mark" x-cloak class="float-right secondary" x-show="showEdit" x-on:click="showEdit = false" title="Cancel editing" />
    @endcan

    <div x-show="!showEdit">
        {{ $view }}
    </div>

    <div x-show="showEdit" x-cloak>
        {{ $edit }}
    </div>
</div>
