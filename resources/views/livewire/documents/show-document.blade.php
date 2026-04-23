<div x-data="{ showView: true }"
     x-on:show-edit="showView = false"
     x-on:close-edit="showView = true"
>
    @can('update', $this->getDocument())
        <livewire:documents.edit-document :slug="$slug" />
    @endcan

    <div x-show="showView">
        @if($title)
            <h1>{{ $title }}</h1>
        @endif
        <div>{!! $content !!}</div>
    </div>
</div>
