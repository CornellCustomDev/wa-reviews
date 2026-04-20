<?php

namespace Tests\Feature\Livewire;

use App\Enums\Roles;
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
        $this->slug = 'test-doc-' . uniqid();
    }

    private function createDocument(): void
    {
        Document::create(['slug' => $this->slug, 'title' => 'Welcome', 'content' => 'Hello', 'is_current' => true, 'version' => 1]);
    }

    #[Test] public function renders_successfully_for_guest()
    {
        $this->createDocument();

        Livewire::test(ShowDocument::class, ['slug' => $this->slug])
            ->assertStatus(200)
            ->assertSeeText('Welcome');
    }

    #[Test] public function slug_is_locked_and_cannot_be_set_by_client()
    {
        $this->createDocument();

        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(ShowDocument::class, ['slug' => $this->slug])
            ->set('slug', ['malicious' => 'array']);
    }

    #[Test] public function unauthenticated_user_cannot_save()
    {
        $this->createDocument();

        Livewire::test(ShowDocument::class, ['slug' => $this->slug])
            ->set('form.title', 'Hacked')
            ->call('save')
            ->assertForbidden();
    }

    #[Test] public function authenticated_admin_can_save()
    {
        $this->getLoggedInTestUser([Roles::SiteAdmin]);
        $this->createDocument();

        Livewire::test(ShowDocument::class, ['slug' => $this->slug])
            ->set('form.title', 'Updated Title')
            ->set('form.content', 'New content')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('documents', ['slug' => $this->slug, 'title' => 'Updated Title']);
    }
}
