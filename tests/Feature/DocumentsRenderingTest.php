<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Models\Document;
use PHPUnit\Framework\Attributes\Test;

class DocumentsRenderingTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Document::create(['slug' => 'document-test', 'title' => 'Welcome', 'content' => 'Hello', 'is_current' => true, 'version' => 1]);
    }

    #[Test]
    public function anonymous_page_contains_show_document_snapshot_but_not_edit_document(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('documents.show-document');
        $response->assertDontSee('documents.edit-document');
    }

    #[Test]
    public function admin_page_contains_both_show_and_edit_document_snapshots(): void
    {
        $this->getLoggedInTestUser([Roles::SiteAdmin]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('documents.show-document');
        $response->assertSee('documents.edit-document');
    }
}
