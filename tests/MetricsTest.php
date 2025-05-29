<?php

declare(strict_types=1);

namespace ReactParallel\Tests;

use PHPUnit\Framework\Attributes\Test;
use ReactParallel\Metrics;
use WyriHaximus\Metrics\Factory as MetricsFactory;
use WyriHaximus\TestUtilities\TestCase;

final class MetricsTest extends TestCase
{
    #[Test]
    public function getters(): void
    {
        $metrics = Metrics::create(MetricsFactory::create());

        /** @phpstan-ignore method.deprecated */
        self::assertSame($metrics->eventLoop, $metrics->eventLoop());
        /** @phpstan-ignore method.deprecated */
        self::assertSame($metrics->infinitePool, $metrics->infinitePool());
    }
}
