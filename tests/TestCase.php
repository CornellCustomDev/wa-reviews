<?php

namespace Tests;

use App\Enums\ProjectStatus;
use App\Enums\Roles;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use InvalidArgumentException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use WithFakeSiteimproveService;

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
