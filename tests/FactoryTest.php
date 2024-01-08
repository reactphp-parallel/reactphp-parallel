<?php

declare(strict_types=1);

namespace ReactParallel\Tests;

use parallel\Channel;
use parallel\Future;
use ReactParallel\Factory;
use ReactParallel\Metrics;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\Metrics\Factory as MetricsFactory;

use function assert;
use function parallel\run;
use function time;

final class FactoryTest extends AsyncTestCase
{
    /** @test */
    public function eventLoopBridge(): void
    {
        $factory = (new Factory())->withMetrics(Metrics::create(MetricsFactory::create()));

        $future = run(static function (int $a, int $b): int {
            return $a * $b;
        }, [333, 2]);
        assert($future instanceof Future);

        self::assertSame(666, $factory->eventLoopBridge()->await($future));
    }

    /** @test */
    public function streams(): void
    {
        $factory = (new Factory())->withMetrics(Metrics::create(MetricsFactory::create()));

        $time    = time();
        $channel = new Channel(Channel::Infinite);
        $channel->send($time);

        self::assertSame($time, $factory->streams()->single($channel));
        $channel->close();
    }

    /** @test */
    public function call(): void
    {
        $factory = (new Factory())->withMetrics(Metrics::create(MetricsFactory::create()));

        self::assertSame(666, $factory->call(static function (int $a, int $b): int {
            return $a * $b;
        }, [333, 2]));
    }

    /** @test */
    public function lowLevelPool(): void
    {
        $factory = (new Factory())->withMetrics(Metrics::create(MetricsFactory::create()));

        $pool = $factory->lowLevelPool();
        self::assertSame(666, $pool->run(static function (int $a, int $b): int {
            return $a * $b;
        }, [333, 2]));
        $pool->close();
    }

    /** @test */
    public function limitedPool(): void
    {
        $factory = (new Factory())->withMetrics(Metrics::create(MetricsFactory::create()));

        $pool = $factory->limitedPool(1);
        self::assertSame(666, $pool->run(static function (int $a, int $b): int {
            return $a * $b;
        }, [333, 2]));
        $pool->close();
    }
}
