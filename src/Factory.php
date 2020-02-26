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
    public static function futureToPromiseConverter(LoopInterface $loop): FutureToPromiseConverter
    {
        return new FutureToPromiseConverter($loop);
    }

    /**
     * @param mixed[] $args
     */
    public static function call(LoopInterface $loop, Closure $closure, array $args = []): PromiseInterface
    {
        $runtime = Runtime::create(self::futureToPromiseConverter($loop));

        /**
         * @psalm-suppress UndefinedInterfaceMethod
         */
        return $runtime->run($closure, $args)->always(static function () use ($runtime): void {
            $runtime->close();
        });
    }

    public static function infinitePool(LoopInterface $loop): Infinite
    {
        return new Infinite($loop, ONE);
    }

    public static function limitedPool(LoopInterface $loop, int $threadCount): Limited
    {
        return Limited::create($loop, $threadCount);
    }
}
