<?php

namespace App\Livewire\Documents;

use App\Livewire\Forms\DocumentForm;
use App\Models\Document;
use Livewire\Attributes\On;
use Livewire\Component;

class ShowDocument extends Component
{
    public string $slug;
    public Document $document;
    public DocumentForm $form;
    public bool $showTitle = false;

    public function mount()
    {
        $this->getDocument();
    }

    #[On('version-updated')]
    public function getDocument(): void
    {
        $this->dispatch('close-edit');
        $this->document = Document::get($this->slug);
        $this->form->setModel($this->document);
    }

    public function save()
    {
        $this->authorize('update', $this->form->document);

        // If the document is new, we create it, otherwise we update the existing one.
        if ($this->form->document->exists === false) {
            $this->document = $this->form->store($this->slug);
        } else {
            $this->document = $this->form->update();
        }

        $this->dispatch('document-updated', ['id' => $this->document->id]);
        $this->dispatch('close-edit');
    }
}
