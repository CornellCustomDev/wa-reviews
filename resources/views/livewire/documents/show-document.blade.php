<div>
    <x-forms.edit-model-wrapper :model="$document">
        <x-slot:view>
            @if($form->title)
                <h1>{{ $form->title }}</h1>
            @endif
            <div>
                {!! $form->content !!}
            </div>
        </x-slot:view>
        <x-slot:edit>
            <form wire:submit="save">
                <x-forms.input label="Title" wire:model="form.title" />
                <x-forms.textarea label="Content" wire:model="form.content" size="lg"/>
                <x-forms.button.submit-group submitName="Update Document" />
            </form>
        </x-slot:edit>
    </x-forms.edit-model-wrapper>
</div>
