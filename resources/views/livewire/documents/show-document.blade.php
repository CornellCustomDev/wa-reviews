<div>
    <x-forms.edit-wrapper>
        <x-slot:view>
            @if($title)
                <h1>{{ $title }}</h1>
            @endif
            <div>{!! $content !!}</div>
        </x-slot:view>

        @can('update', $this->getDocument())
            <x-slot:edit>
                <livewire:documents.edit-document :slug="$slug" />
            </x-slot:edit>
        @endcan
    </x-forms.edit-wrapper>
</div>
