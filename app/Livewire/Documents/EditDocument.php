<?php

namespace App\Livewire\Documents;

use App\Livewire\Forms\DocumentForm;
use App\Models\Document;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class EditDocument extends Component
{
    #[Locked]
    public string $slug;

    public Document $document;

    public DocumentForm $form;

    public function mount(): void
    {
        $this->document = Document::get($this->slug);
        $this->authorize('update', $this->document);
        $this->form->setModel($this->document);
    }

    #[On('version-updated')]
    public function getDocument(): void
    {
        $this->document = Document::get($this->slug);
        $this->form->setModel($this->document);
    }

    public function save(): void
    {
        $this->authorize('update', $this->form->document);

        if ($this->form->document->exists === false) {
            $this->document = $this->form->store($this->slug);
        } else {
            $this->document = $this->form->update();
        }

        $this->dispatch('version-updated');
        $this->dispatch('close-edit');
    }
}
