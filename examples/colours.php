<?php


use PackageVersions\Versions;
use React\EventLoop\Factory;
use ReactParallel\Factory as ParallelFactory;
use ReactParallel\ObjectProxy\Generated\Proxies\WyriHaximus\Metrics\Registry as RegistryProxy;
use WyriHaximus\Metrics\Label;
use function React\Promise\all;
use WyriHaximus\React\Parallel\Finite;
use function WyriHaximus\iteratorOrArrayToArray;
use WyriHaximus\React\Parallel\ReturnThread;
use WyriHaximus\React\Parallel\FiniteWorker;

$options = getopt(
    '',
    [
        'iterations:',
        'delay::',
    ],
);

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$loop = Factory::create();
echo 'Loop: ', get_class($loop), PHP_EOL;

$parallelFactory = new ParallelFactory($loop);
$pool = $parallelFactory->lowLevelPool();

$loop->futureTick(static function () use ($pool, $options): void {
    foreach (range(0, 7) as $i) {
        $pool->run(static function (int $index, int $iterations, bool $delay): int {
            for ($i = 0; $i < $iterations; $i++) {
                if ($delay) {
                    usleep($i * 3.3);
                }
                echo "\033[" . (30 + $index) . ";" . (40 + $index) . "m.\033[0m";
            }
            return true;
        }, [$i, (int)$options['iterations'], isset($options['delay'])]);
    }
});

echo PHP_EOL, 'Loop::run()', PHP_EOL;
$loop->run();
echo PHP_EOL, 'Loop::done()', PHP_EOL;