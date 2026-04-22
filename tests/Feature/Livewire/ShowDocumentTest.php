<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Documents\ShowDocument;
use App\Models\Document;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

class ShowDocumentTest extends FeatureTestCase
{
    private string $slug;

    protected function setUp(): void
    {
        parent::setUp();
        $this->slug = 'test-doc-'.uniqid();
    }

    private function createDocument(): void
    {
        Document::create(['slug' => $this->slug, 'title' => 'Welcome', 'content' => 'Hello', 'is_current' => true, 'version' => 1]);
    }

    #[Test]
    public function renders_successfully_for_guest(): void
    {
        $this->createDocument();

        Livewire::test(ShowDocument::class, ['slug' => $this->slug])
            ->assertStatus(200)
            ->assertSeeText('Welcome');
    }

    #[Test]
    public function slug_is_locked_and_cannot_be_set_by_client(): void
    {
        $this->createDocument();

        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(ShowDocument::class, ['slug' => $this->slug])
            ->set('slug', ['malicious' => 'array']);
    }
}
