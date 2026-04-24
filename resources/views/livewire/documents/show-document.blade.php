<div>
    <x-forms.edit-wrapper>
        <x-slot:view>
            @if($document->title)
                <h1>{{ $document->title }}</h1>
            @endif
            <div>{!! $document->content !!}</div>
        </x-slot:view>

        @can('update', $document)
            <x-slot:edit>
                <livewire:documents.edit-document :slug="$document->slug" />
            </x-slot:edit>
        @endcan
    </x-forms.edit-wrapper>
</div>
