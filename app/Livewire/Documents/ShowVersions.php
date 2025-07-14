<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ShowVersions extends Component
{
    public Document $document;

    public function mount(string $slug)
    {
        $this->document = Document::get($slug);
    }

    #[Computed]
    public function versions(): Collection
    {
        return $this->document->versions()
            ->select(['id', 'is_current', 'created_at'])
            ->get();
    }

    #[On('document-updated')]
    public function refreshVersions(): void
    {
        $this->document = Document::get($this->document->slug);
    }

    public function makeCurrentVersion(int $id): void
    {
        $this->authorize('update', $this->document);

        $this->document->setCurrentVersion($id);
        $this->dispatch('version-updated', ['id' => $id]);
    }
}
