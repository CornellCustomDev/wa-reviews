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

    public function mount(): void
    {
        $this->refreshDocument();
    }

    #[Computed]
    public function getDocument(): Document
    {
        return Document::get($this->slug);
    }

    #[On('version-updated')]
    public function refreshDocument(): void
    {
        $document = $this->getDocument();
        $this->title = $document->title;
        $this->content = $document->content ?? '';
    }
}
