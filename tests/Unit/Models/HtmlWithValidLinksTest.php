<?php

namespace Tests\Unit\Models;

use App\Casts\HtmlWithValidLinks;
use App\Models\Issue;
use App\Models\Item;
use App\Models\Scope;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Unit\TestDatabase;

class HtmlWithValidLinksTest extends TestCase
{
    use TestDatabase;

    /**
     * An editor link whose href absorbed escaped markup, alongside valid links.
     */
    private const string HTML_WITH_INVALID_LINK = '<p>Contact <a target="_blank" rel="noopener noreferrer nofollow" href="mailto:someone@example.com&quot;&gt;someone@example.com&lt;/a&gt;">Someone</a>, <a href="mailto:someone@example.com">email</a>, or <a href="https://example.com/#help">help</a>.</p>';

    private const string HTML_WITHOUT_INVALID_LINK = '<p>Contact <a href="mailto:someone@example.com">email</a>, or <a href="https://example.com/#help">help</a>.</p>';

    #[Test]
    public function removes_invalid_links_and_keeps_their_text(): void
    {
        $html = HtmlWithValidLinks::removeInvalidLinks(self::HTML_WITH_INVALID_LINK);

        $this->assertEquals('<p>Contact Someone, <a href="mailto:someone@example.com">email</a>, or <a href="https://example.com/#help">help</a>.</p>', $html);
    }

    #[Test]
    public function leaves_html_without_invalid_links_unchanged(): void
    {
        $this->assertSame(self::HTML_WITHOUT_INVALID_LINK, HtmlWithValidLinks::removeInvalidLinks(self::HTML_WITHOUT_INVALID_LINK));
    }

    #[Test]
    public function validates_link_uris(): void
    {
        $this->assertTrue(HtmlWithValidLinks::isValidLinkUri('https://cornell.infoready-preprod.com/#competitionDetail/1855674'));
        $this->assertTrue(HtmlWithValidLinks::isValidLinkUri('mailto:someone@example.com'));
        $this->assertTrue(HtmlWithValidLinks::isValidLinkUri('tel:607-254-2500'));

        $this->assertFalse(HtmlWithValidLinks::isValidLinkUri('mailto:someone@example.com">someone@example.com</a>'));
        $this->assertFalse(HtmlWithValidLinks::isValidLinkUri('https://example.com/has space'));
        $this->assertFalse(HtmlWithValidLinks::isValidLinkUri('example.com'));
        $this->assertFalse(HtmlWithValidLinks::isValidLinkUri("mailto:someone@example.com\x01"));
        $this->assertFalse(HtmlWithValidLinks::isValidLinkUri('mailto:someone%zz@example.com'));
        $this->assertTrue(HtmlWithValidLinks::isValidLinkUri('mailto:someone@example.com?subject=Hello%20there'));
    }

    #[Test]
    public function removes_invalid_links_with_uppercase_tags(): void
    {
        $html = HtmlWithValidLinks::removeInvalidLinks('<p><A HREF="mailto:a@b.edu&quot;&gt;">Someone</A></p>');

        $this->assertEquals('<p>Someone</p>', $html);
    }

    #[Test]
    public function cloned_issue_is_stored_without_invalid_links(): void
    {
        // Content stored before the cast existed, which replicate() copies as raw attributes
        $issue = Issue::factory()->create();
        Issue::query()->whereKey($issue->id)->update(['recommendation' => self::HTML_WITH_INVALID_LINK]);

        $clonedIssue = $issue->fresh()->replicate(except: ['guideline_instance']);
        $clonedIssue->save();

        $this->assertStringContainsString('Contact Someone,', Issue::query()->toBase()->find($clonedIssue->id)->recommendation);
    }

    #[Test]
    public function issue_html_fields_are_stored_without_invalid_links_on_create_and_update(): void
    {
        // AI agents and tools create and update issues with Eloquent attribute arrays, as here
        $issue = Issue::factory()->create([
            'description' => self::HTML_WITH_INVALID_LINK,
            'testing' => self::HTML_WITH_INVALID_LINK,
            'ai_reasoning' => self::HTML_WITH_INVALID_LINK,
        ]);
        $issue->update(['recommendation' => self::HTML_WITH_INVALID_LINK]);

        $stored = Issue::query()->toBase()->find($issue->id);
        foreach (['description', 'recommendation', 'testing', 'ai_reasoning'] as $field) {
            $this->assertStringNotContainsString('someone@example.com&quot;', $stored->$field, $field);
            $this->assertStringContainsString('Contact Someone,', $stored->$field, $field);
        }
    }

    #[Test]
    public function logs_each_removed_link_with_the_content_it_came_from(): void
    {
        $issue = Issue::factory()->create();
        Log::spy();

        $issue->update(['recommendation' => self::HTML_WITH_INVALID_LINK]);

        Log::shouldHaveReceived('warning')->once()->with('Removed invalid link from HTML content', [
            'model' => Issue::class,
            'id' => $issue->id,
            'field' => 'recommendation',
            'href' => 'mailto:someone@example.com">someone@example.com</a>',
            'text' => 'Someone',
        ]);
    }

    #[Test]
    public function does_not_log_when_no_links_are_removed(): void
    {
        Log::spy();

        Issue::factory()->create(['recommendation' => self::HTML_WITHOUT_INVALID_LINK]);

        Log::shouldNotHaveReceived('warning');
    }

    #[Test]
    public function item_html_fields_are_stored_without_invalid_links(): void
    {
        $item = Item::factory()->create(['recommendation' => self::HTML_WITH_INVALID_LINK]);

        $this->assertStringContainsString('Contact Someone,', Item::query()->toBase()->find($item->id)->recommendation);
    }

    #[Test]
    public function scope_notes_are_stored_without_invalid_links(): void
    {
        $scope = Scope::factory()->create(['notes' => self::HTML_WITH_INVALID_LINK]);

        $this->assertStringContainsString('Contact Someone,', Scope::query()->toBase()->find($scope->id)->notes);
    }
}
