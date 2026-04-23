@props([
    'edit' => false,
    'view' => null,
])
<div x-data="{ showView: true }"
     x-on:show-edit="showView = false"
     x-on:close-edit="showView = true"
>
    @if($edit)
        <div x-data="{ showEdit: false }"
             x-on:show-edit="showEdit = true"
             x-on:close-edit="showEdit = false"
        >
            <x-forms.button icon="pencil-square" class="float-right" title="Edit"
                            x-show="!showEdit" x-on:click="$dispatch('show-edit')"
            />
            <div x-show="showEdit" x-cloak>
                <x-forms.button icon="x-mark" class="float-right secondary" title="Cancel editing"
                                x-on:click="$dispatch('close-edit')"
                />
                {{ $edit }}
            </div>
        </div>
    @endif

    <div x-show="showView">
        {{ $view }}
    </div>
</div>
