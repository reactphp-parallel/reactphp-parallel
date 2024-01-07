<?php

declare(strict_types=1);

namespace ReactParallel;

use ReactParallel\EventLoop\Metrics as EventLoopMetrics;
use ReactParallel\Pool\Infinite\Metrics as InfinitePoolMetrics;
use WyriHaximus\Metrics\Registry;

final class Metrics
{
    public function __construct(private EventLoopMetrics $eventLoop, private InfinitePoolMetrics $infinitePool)
    {
    }

    public static function create(Registry $registry): self
    {
        return new self(
            EventLoopMetrics::create($registry),
            InfinitePoolMetrics::create($registry),
        );
    }

    public function eventLoop(): EventLoopMetrics
    {
        return $this->eventLoop;
    }

    public function infinitePool(): InfinitePoolMetrics
    {
        return $this->infinitePool;
    }
}
