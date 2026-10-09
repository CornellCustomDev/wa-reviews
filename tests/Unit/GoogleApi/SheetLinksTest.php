<?php

namespace Tests\Unit\GoogleApi;

use App\Services\GoogleApi\Helpers\SheetLinks;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SheetLinksTest extends TestCase
{
    /**
     * An editor link whose href absorbed escaped markup, alongside valid links.
     */
    private const string HTML_WITH_INVALID_LINK = '<p>Contact <a target="_blank" rel="noopener noreferrer nofollow" href="mailto:someone@example.com&quot;&gt;someone@example.com&lt;/a&gt;">Someone</a>, <a href="mailto:someone@example.com">email</a>, or <a href="https://example.com/#help">help</a>.</p>';

    #[Test]
    public function validates_link_uris(): void
    {
        $this->assertTrue(SheetLinks::isValid('https://cornell.infoready-preprod.com/#competitionDetail/1855674'));
        $this->assertTrue(SheetLinks::isValid('mailto:someone@example.com?subject=Hello%20there'));
        $this->assertTrue(SheetLinks::isValid('tel:607-254-2500'));

        $this->assertFalse(SheetLinks::isValid('mailto:someone@example.com">someone@example.com</a>'));
        $this->assertFalse(SheetLinks::isValid("mailto:someone@example.com\x01"));
        $this->assertFalse(SheetLinks::isValid('example.com'));
        $this->assertFalse(SheetLinks::isValid('javascript:alert(1)'));
    }

    #[Test]
    public function normalizes_link_uris_that_need_percent_encoding(): void
    {
        $this->assertEquals('https://example.com/has%20space', SheetLinks::normalize('https://example.com/has space'));
    }

    #[Test]
    public function finds_only_invalid_links_in_html(): void
    {
        $this->assertEquals(
            [['text' => 'Someone', 'href' => 'mailto:someone@example.com">someone@example.com</a>']],
            SheetLinks::invalidLinksIn(self::HTML_WITH_INVALID_LINK),
        );
        $this->assertEquals([], SheetLinks::invalidLinksIn('<p><a href="https://example.com">fine</a></p>'));
        $this->assertEquals([], SheetLinks::invalidLinksIn(null));
    }

    #[Test]
    public function finds_invalid_links_with_uppercase_tags(): void
    {
        $this->assertCount(1, SheetLinks::invalidLinksIn('<p><A HREF="mailto:a@b.edu&quot;&gt;">Someone</A></p>'));
    }

    #[Test]
    public function describes_invalid_links_by_field(): void
    {
        $this->assertEquals(
            ['Recommendations: "Someone"'],
            SheetLinks::describeInvalidLinks(['Description' => '<p>No links</p>', 'Recommendations' => self::HTML_WITH_INVALID_LINK]),
        );
    }
}
