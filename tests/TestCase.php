<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use InvalidArgumentException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected static function fixture(string $name, bool $json = false): array|string
    {
        $contents = file_get_contents(
            filename: base_path("tests/Fixtures/$name"),
        );

        if(!$contents) {
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
