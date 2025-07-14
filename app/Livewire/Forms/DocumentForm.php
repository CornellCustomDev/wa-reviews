<?php

namespace App\Livewire\Forms;

use App\Events\DocumentChanged;
use App\Models\Document;
use Livewire\Attributes\Validate;
use Livewire\Form;

class DocumentForm extends Form
{
    public ?Document $document;

    #[Validate('string|max:255', as: 'Document title')]
    public string $title = '';
    public ?string $content = null;

    public function setModel(Document $document): void
    {
        $this->document = $document;
        $this->title = $document->title;
        $this->content = $document->content;
    }

    public function store(string $slug): Document
    {
        $this->validate();

        $this->document = Document::create(['slug' => $slug, ...$this->all()]);

        event(new DocumentChanged($this->document, 'created'));

        return $this->document;
    }

    public function update(): Document
    {
        $this->validate();

        $originalDocument = $this->document;
        $this->document = $originalDocument->createNewVersion($this->all());

        event(new DocumentChanged($this->document, 'new_version'));

        return $this->document;
    }

}
