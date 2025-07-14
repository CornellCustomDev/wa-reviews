<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ShowVersions extends Component
{
    public string $slug;
    public Document $document;

    public function mount(string $slug)
    {
        $this->document = Document::get($slug);
    }

    #[Computed]
    public function versions(): Collection
    {
        return Document::versions($this->slug)
            ->select(['id', 'version', 'is_current', 'created_at'])
            ->get();
    }

    #[On('document-updated')]
    public function refreshVersions(): void
    {
        unset($this->versions);
        $this->document = Document::get($this->slug);
    }

    public function makeCurrentVersion(int $id): void
    {
        $this->authorize('update', $this->document);

        $this->document = $this->document->setCurrentVersion($id);
        unset($this->versions);

        $this->dispatch('version-updated', ['id' => $id]);
    }
}
