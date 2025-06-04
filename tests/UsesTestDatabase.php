<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

trait UsesTestDatabase
{
    use RefreshDatabase;

    protected array $connectionsToTransact = ['sqlite'];

    public function beforeRefreshingDatabase(): void
    {
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
        config(['telescope.storage.database.connection' => 'sqlite']);
    }
}
