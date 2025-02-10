<?php

declare(strict_types=1);

use ReactParallel\Factory;

use function PHPStan\Testing\assertType;

$factory = new Factory();

assertType('Closure(): void', (static fn () => $factory->call(static function (): void {
    sleep(1);
})));

assertType('Closure(): void', (static fn () => $factory->call(static function (int $time): void {
    sleep($time);
}, [1])));

assertType('true', $factory->call(static function (): bool {
    return true;
}));

assertType('int<1, max>|true', $factory->call(static function (): bool|int {
    return time() % 2 !== 0 ? true : time();
}));

assertType('int<1, max>|true', $factory->call(static function (int $mod): bool|int {
    return time() % $mod !== 0 ? true : time();
}, [2]));

assertType('bool|int<1, max>', $factory->call(static function (int $mod, bool $yolo): bool|int {
    return time() % $mod !== 0 ? $yolo : time();
}, [2, (time() % 13 !== 0)]));

assertType('bool|non-empty-string', $factory->call(static function (int $mod, bool $yolo, string $oloy): bool|string {
    return time() % $mod !== 0 ? $yolo : $oloy;
}, [2, (time() % 13 !== 0), bin2hex(random_bytes(13))]));
