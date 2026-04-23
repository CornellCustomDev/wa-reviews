<?php

namespace Tests\Feature\Livewire;

use App\Enums\Roles;
use App\Livewire\Documents\EditDocument;
use App\Models\Document;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

class EditDocumentTest extends FeatureTestCase
{
    private string $slug;

    protected function setUp(): void
    {
        parent::setUp();
        $this->slug = 'test-doc-'.uniqid();
        Document::create(['slug' => $this->slug, 'title' => 'Original', 'content' => 'Body', 'is_current' => true, 'version' => 1]);
    }

    #[Test]
    public function unauthenticated_user_cannot_mount(): void
    {
        Livewire::test(EditDocument::class, ['slug' => $this->slug])
            ->assertForbidden();
    }

    #[Test]
    public function non_admin_authenticated_user_cannot_mount(): void
    {
        $this->getLoggedInTestUser([Roles::TeamAdmin]);

        Livewire::test(EditDocument::class, ['slug' => $this->slug])
            ->assertForbidden();
    }

    #[Test]
    public function admin_can_mount_and_save(): void
    {
        $this->getLoggedInTestUser([Roles::SiteAdmin]);

        Livewire::test(EditDocument::class, ['slug' => $this->slug])
            ->set('form.title', 'Updated Title')
            ->set('form.content', 'New content')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('version-updated')
            ->assertDispatched('close-edit');

        $this->assertDatabaseHas('documents', ['slug' => $this->slug, 'title' => 'Updated Title']);
    }

    #[Test]
    public function slug_is_locked_and_cannot_be_tampered(): void
    {
        $this->getLoggedInTestUser([Roles::SiteAdmin]);

        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(EditDocument::class, ['slug' => $this->slug])
            ->set('slug', ['malicious' => 'array']);
    }

    #[Test]
    public function save_requires_authorization_on_subsequent_request(): void
    {
        $this->getLoggedInTestUser([Roles::SiteAdmin]);

        $component = Livewire::test(EditDocument::class, ['slug' => $this->slug]);

        // Simulate loss of authentication between mount and action
        $this->app['auth']->forgetGuards();
        auth()->logout();

        $component
            ->call('save')
            ->assertForbidden();
    }
}
