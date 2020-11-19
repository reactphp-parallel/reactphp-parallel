<?php declare(strict_types=1);

namespace ReactParallel\Tests;

use parallel\Channel;
use parallel\Future;
use React\EventLoop\Factory as EventLoopFactory;
use ReactParallel\Factory;
use ReactParallel\Metrics;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\Metrics\Factory as MetricsFactory;
use function parallel\run;

final class FactoryTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function eventLoopBridge(): void
    {
        $factory = (new Factory(EventLoopFactory::create()))->withMetrics(Metrics::create(MetricsFactory::create()));

        $future = run(function (int $a, int $b): int {
            return $a * $b;
        }, [333, 2]);
        assert($future instanceof Future);

        self::assertSame(666, $this->await($factory->eventLoopBridge()->await($future), $factory->loop()));
    }

    /**
     * @test
     */
    public function streams(): void
    {
        $factory = (new Factory(EventLoopFactory::create()))->withMetrics(Metrics::create(MetricsFactory::create()));

        $time = time();
        $channel = new Channel(Channel::Infinite);
        $channel->send($time);

        self::assertSame($time, $this->await($factory->streams()->single($channel), $factory->loop()));
    }

    /**
     * @test
     */
    public function call(): void
    {
        $factory = (new Factory(EventLoopFactory::create()))->withMetrics(Metrics::create(MetricsFactory::create()));

        self::assertSame(666, $this->await($factory->call(function (int $a, int $b): int {
            return $a * $b;
        }, [333, 2]), $factory->loop()));
    }

    /**
     * @test
     */
    public function lowLevelPool(): void
    {
        $factory = (new Factory(EventLoopFactory::create()))->withMetrics(Metrics::create(MetricsFactory::create()));

        $pool = $factory->lowLevelPool();
        self::assertSame(666, $this->await($pool->run(function (int $a, int $b): int {
            return $a * $b;
        }, [333, 2]), $factory->loop()));
        $pool->close();
    }

    /**
     * @test
     */
    public function limitedPool(): void
    {
        $factory = (new Factory(EventLoopFactory::create()))->withMetrics(Metrics::create(MetricsFactory::create()));

        $pool = $factory->limitedPool(1);
        self::assertSame(666, $this->await($pool->run(function (int $a, int $b): int {
            return $a * $b;
        }, [333, 2]), $factory->loop()));
        $pool->close();
    }
}
