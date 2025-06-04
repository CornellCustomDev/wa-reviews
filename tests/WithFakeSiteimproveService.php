<?php

namespace Tests;

use App\Services\SiteImprove\SiteimproveService;
use Illuminate\Http\Client\Response;
use Mockery;

trait WithFakeSiteimproveService
{
    /**
     * Mocks the SiteimproveService get method to return a given value (default: []).
     */
    protected function fakeSiteimproveService($items = []): void
    {
        $mock = Mockery::mock(SiteimproveService::class)->makePartial();
        $mock->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('get')
            ->andReturn(new Response(new \GuzzleHttp\Psr7\Response(200, [], json_encode(['items' => $items]))));
        $this->app->instance(SiteimproveService::class, $mock);
    }
}
