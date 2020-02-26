<?php declare(strict_types=1);

namespace ReactParallel\Tests;

use React\EventLoop\Factory as EventLoopFactory;
use ReactParallel\Factory;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

/**
 * @internal
 */
final class FactoryTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function call(): void
    {
        $loop = EventLoopFactory::create();

        /**
         * Infection trick time
         */
        Factory::futureToPromiseConverter($loop);

        self::assertSame(666, $this->await(Factory::call($loop, function (int $a, int $b): int {
            return $a * $b;
        }, [333, 2]), $loop));
    }

    /**
     * @test
     */
    public function infinitePool(): void
    {

        $loop = EventLoopFactory::create();

        /**
         * Infection trick time
         */
        Factory::futureToPromiseConverter($loop);

        $pool = Factory::infinitePool($loop);
        self::assertSame(666, $this->await($pool->run(function (int $a, int $b): int {
            return $a * $b;
        }, [333, 2]), $loop));
        $pool->close();
    }

    /**
     * @test
     */
    public function limitedPool(): void
    {

        $loop = EventLoopFactory::create();

        /**
         * Infection trick time
         */
        Factory::futureToPromiseConverter($loop);

        $pool = Factory::limitedPool($loop, 1);
        self::assertSame(666, $this->await($pool->run(function (int $a, int $b): int {
            return $a * $b;
        }, [333, 2]), $loop));
        $pool->close();
    }
}
