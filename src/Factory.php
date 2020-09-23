<?php

declare(strict_types=1);

namespace ReactParallel;

use Closure;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use ReactParallel\Contracts\LowLevelPoolInterface;
use ReactParallel\EventLoop\EventLoopBridge;
use ReactParallel\Pool\Infinite\Infinite;
use ReactParallel\Pool\Limited\Limited;
use ReactParallel\Streams\Factory as StreamsFactory;

use const WyriHaximus\Constants\Numeric\ONE;

final class Factory
{
    private LoopInterface $loop;
    private ?Metrics $metrics                    = null;
    private ?EventLoopBridge $eventLoopBridge    = null;
    private ?LowLevelPoolInterface $infinitePool = null;
    private ?StreamsFactory $streamsFactory      = null;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function withMetrics(Metrics $metrics): self
    {
        $self          = clone $this;
        $self->metrics = $metrics;

        return $self;
    }

    public function loop(): LoopInterface
    {
        return $this->loop;
    }

    public function eventLoopBridge(): EventLoopBridge
    {
        if ($this->eventLoopBridge === null) {
            $this->eventLoopBridge = new EventLoopBridge($this->loop);
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

    /**
     * @param mixed[] $args
     */
    public function call(Closure $closure, array $args = []): PromiseInterface
    {
        return $this->lowLevelPool()->run($closure, $args);
    }

    public function lowLevelPool(): LowLevelPoolInterface
    {
        if ($this->infinitePool === null) {
            $this->infinitePool = new Infinite($this->loop, $this->eventLoopBridge(), ONE);
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
