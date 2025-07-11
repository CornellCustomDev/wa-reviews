<?php

namespace App\Events;

use App\Models\Document;

class DocumentChanged extends AbstractModelChanged
{
    public function __construct(
        private readonly Document $document,
        string $action,
        ?array $delta = null,
        mixed $actor = null,
    ) {
        parent::__construct($document, $action, $delta, $actor);
    }

    public function getContextType(): string
    {
        return Document::class;
    }

    protected function getContextId(): int
    {
        return $this->document->id;
    }
}
