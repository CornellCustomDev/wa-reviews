<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class ShowDocument extends Component
{
    #[Locked]
    public string $slug;
    public ?string $title;
    public string $content;

    public bool $showEdit = false;

    public function mount(): void
    {
        $this->getDocument();
    }

    #[On('version-updated')]
    #[Computed]
    public function getDocument(): Document
    {
        $document = Document::get($this->slug);
        $this->title = $document->title;
        $this->content = $document->content;

        return $document;
    }
}
