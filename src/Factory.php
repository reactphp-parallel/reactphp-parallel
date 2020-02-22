<?php declare(strict_types=1);

namespace ReactParallel;

use Closure;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use ReactParallel\FutureToPromiseConverter\FutureToPromiseConverter;
use ReactParallel\Runtime\Runtime;

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
}
