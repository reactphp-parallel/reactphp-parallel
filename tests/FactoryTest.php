<?php declare(strict_types=1);

namespace ReactParallel\Tests;

use parallel\Channel;
use parallel\Future;
use React\EventLoop\Factory as EventLoopFactory;
use ReactParallel\Factory;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use function parallel\run;

final class FactoryTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function eventLoopBridge(): void
    {
        $loop = EventLoopFactory::create();
        $factory = new Factory($loop);

        $future = run(function (int $a, int $b): int {
            return $a * $b;
        }, [333, 2]);
        assert($future instanceof Future);

        self::assertSame(666, $this->await($factory->eventLoopBridge()->await($future), $loop));
    }

    /**
     * @test
     */
    public function streams(): void
    {
        $loop = EventLoopFactory::create();
        $factory = new Factory($loop);

        $time = time();
        $channel = new Channel(Channel::Infinite);
        $channel->send($time);

        self::assertSame($time, $this->await($factory->streams()->single($channel), $loop));
    }

    /**
     * @test
     */
    public function call(): void
    {
        $loop = EventLoopFactory::create();
        $factory = new Factory($loop);

        self::assertSame(666, $this->await($factory->call(function (int $a, int $b): int {
            return $a * $b;
        }, [333, 2]), $loop));
    }

    /**
     * @test
     */
    public function infinitePool(): void
    {
        $loop = EventLoopFactory::create();
        $factory = new Factory($loop);

        $pool = $factory->infinitePool();
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
        $factory = new Factory($loop);

        $pool = $factory->limitedPool(1);
        self::assertSame(666, $this->await($pool->run(function (int $a, int $b): int {
            return $a * $b;
        }, [333, 2]), $loop));
        $pool->close();
    }
}
