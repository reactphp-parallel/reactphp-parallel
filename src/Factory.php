<?php declare(strict_types=1);

namespace ReactParallel;

use Closure;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use ReactParallel\FutureToPromiseConverter\FutureToPromiseConverter;
use ReactParallel\Pool\Infinite\Infinite;
use ReactParallel\Pool\Limited\Limited;
use ReactParallel\Runtime\Runtime;
use const WyriHaximus\Constants\Numeric\ONE;

final class Factory
{
    private ?FutureToPromiseConverter $futureToPromiseConverter = null;
    private ?Infinite $infinitePool                             = null;

    public function futureToPromiseConverter(LoopInterface $loop): FutureToPromiseConverter
    {
        if ($this->futureToPromiseConverter === null) {
            $this->futureToPromiseConverter = new FutureToPromiseConverter($loop);
        }

        return $this->futureToPromiseConverter;
    }

    /**
     * @param mixed[] $args
     */
    public function call(LoopInterface $loop, Closure $closure, array $args = []): PromiseInterface
    {
        $runtime = Runtime::create($this->futureToPromiseConverter($loop));

        /**
         * @psalm-suppress UndefinedInterfaceMethod
         */
        return $runtime->run($closure, $args)->always(static function () use ($runtime): void {
            $runtime->close();
        });
    }

    public function infinitePool(LoopInterface $loop): Infinite
    {
        if ($this->infinitePool === null) {
            $this->infinitePool = new Infinite($loop, ONE);
        }

        return $this->infinitePool;
    }

    public function limitedPool(LoopInterface $loop, int $threadCount): Limited
    {
        return Limited::createWithPool($this->infinitePool($loop), $threadCount);
    }
}
