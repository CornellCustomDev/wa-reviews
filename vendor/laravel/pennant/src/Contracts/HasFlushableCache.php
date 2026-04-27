<?php

namespace Laravel\Pennant\Contracts;

interface HasFlushableCache
{
    /**
     * Flush the cache.
     *
     * @return void
     */
    public function flushCache();
}
