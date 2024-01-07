<?php

declare(strict_types=1);

namespace ReactParallel;

use Closure;
use ReactParallel\Contracts\LowLevelPoolInterface;
use ReactParallel\EventLoop\EventLoopBridge;
use ReactParallel\Pool\Infinite\Infinite;
use ReactParallel\Pool\Limited\Limited;
use ReactParallel\Streams\Factory as StreamsFactory;

final class Factory
{
    private const LOW_LEVEL_POOL_TTL = 0.666;

    private Metrics|null $metrics                    = null;
    private EventLoopBridge|null $eventLoopBridge    = null;
    private LowLevelPoolInterface|null $infinitePool = null;
    private StreamsFactory|null $streamsFactory      = null;

    public function withMetrics(Metrics $metrics): self
    {
        $self          = clone $this;
        $self->metrics = $metrics;

        return $self;
    }

    public function eventLoopBridge(): EventLoopBridge
    {
        if ($this->eventLoopBridge === null) {
            $this->eventLoopBridge = new EventLoopBridge();
            if ($this->metrics instanceof Metrics) {
                $this->eventLoopBridge = $this->eventLoopBridge->withMetrics($this->metrics->eventLoop());
            }
        }

        return $this->eventLoopBridge;
    }

    public function streams(): StreamsFactory
    {
        if ($this->streamsFactory === null) {
            $this->streamsFactory = new StreamsFactory($this->eventLoopBridge());
        }

        return $this->streamsFactory;
    }

    /** @param mixed[] $args */
    public function call(Closure $closure, array $args = []): mixed
    {
        return $this->lowLevelPool()->run($closure, $args);
    }

    public function lowLevelPool(): LowLevelPoolInterface
    {
        if ($this->infinitePool === null) {
            $this->infinitePool = new Infinite($this->eventLoopBridge(), self::LOW_LEVEL_POOL_TTL);
            if ($this->metrics instanceof Metrics) {
                $this->infinitePool = $this->infinitePool->withMetrics($this->metrics->infinitePool());
            }
        }

        return $this->infinitePool;
    }

    public function limitedPool(int $threadCount): Limited
    {
        return new Limited($this->lowLevelPool(), $threadCount);
    }
}
