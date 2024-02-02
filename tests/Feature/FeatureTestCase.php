<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\CreatesApplication;
use Tests\TestCase;

class FeatureTestCase extends TestCase
{
    use CreatesApplication;
    use DatabaseTransactions;
}
