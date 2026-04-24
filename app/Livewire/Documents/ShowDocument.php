<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class ShowDocument extends Component
{
    #[Locked] public Document $document;

    public function mount(string $slug): void
    {
        $this->document = Document::get($slug);
    }

    #[On('version-updated')]
    public function refreshDocument(): void
    {
        $this->document = Document::get($this->document->slug);
    }
}
