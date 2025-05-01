<?php

namespace LarAgent\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \LarAgent\LarAgent
 */
class LarAgent extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \LarAgent\LarAgent::class;
    }
}
