<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class ShowDocument extends Component
{
    #[Locked]
    public string $slug;

    public Document $document;

    public function mount(): void
    {
        $this->getDocument();
    }

    #[On('version-updated')]
    public function getDocument(): void
    {
        $this->document = Document::get($this->slug);
    }
}
