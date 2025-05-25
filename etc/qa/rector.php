<?php

declare(strict_types=1);

use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use WyriHaximus\TestUtilities\RectorConfig;

return RectorConfig::configure(dirname(__DIR__, 2))->withSkip([
    ClosureToArrowFunctionRector::class,
]);
