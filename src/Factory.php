<?php

declare(strict_types=1);

namespace ReactParallel;

use Closure;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use ReactParallel\EventLoop\EventLoopBridge;
use ReactParallel\Pool\Infinite\Infinite;
use ReactParallel\Pool\Limited\Limited;
use ReactParallel\Streams\Factory as StreamsFactory;

use const WyriHaximus\Constants\Numeric\ONE;

final class Factory
{
    private LoopInterface $loop;
    private ?EventLoopBridge $eventLoopBridge = null;
    private ?Infinite $infinitePool           = null;
    private ?StreamsFactory $streamsFactory   = null;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function loop(): LoopInterface
    {
        return $this->loop;
    }

    public function eventLoopBridge(): EventLoopBridge
    {
        if ($this->eventLoopBridge === null) {
            $this->eventLoopBridge = new EventLoopBridge($this->loop);
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
        return $this->infinitePool()->run($closure, $args);
    }

    public function infinitePool(): Infinite
    {
        if ($this->infinitePool === null) {
            $this->infinitePool = new Infinite($this->loop, $this->eventLoopBridge(), ONE);
        }

        return $this->infinitePool;
    }

    public function limitedPool(int $threadCount): Limited
    {
        return Limited::createWithPool($this->infinitePool(), $threadCount);
    }
}
