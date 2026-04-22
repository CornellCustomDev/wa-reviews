<div>
    @if($document->title)
        <h1>{{ $document->title }}</h1>
    @endif
    <div>{!! $document->content !!}</div>

    @can('update', $document)
        <livewire:documents.edit-document :slug="$slug" />
    @endcan
</div>
