<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use InvalidArgumentException;
use Laravel\Pennant\Feature;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use WithFakeSiteimproveService;

    protected function setUp(): void
    {
        parent::setUp();
        Feature::activate('verification-reviews');
        Feature::activate('comments');
    }

    protected static function fixture(string $name, bool $json = false): array|string
    {
        $contents = file_get_contents(
            filename: base_path("tests/Fixtures/$name"),
        );

        if (! $contents) {
            throw new InvalidArgumentException(
                message: "Cannot find fixture: tests/Fixtures/$name",
            );
        }

        return $json ? json_decode(
            json: $contents,
            associative: true,
        ) : $contents;
    }
}
