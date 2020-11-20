<?php

use Ancarda\Psr7\StringStream\StringStream;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Message\Response;
use React\Http\Server as HttpServer;
use React\Promise\PromiseInterface;
use React\Socket\Server as SocketServer;
use ReactParallel\Factory as ReactParallelFactory;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
$loop = Factory::create();
$pool = (new ReactParallelFactory($loop))->lowLevelPool();

$server = new HttpServer($loop, static function (ServerRequestInterface $request) use ($pool): PromiseInterface {
    return $pool->run(function () {
        return new Response(200, [], new StringStream("Hello World!\n"));
    }, []);
});

$socket = new SocketServer('0.0.0.0:8080', $loop, ['tcp' => ['so_reuseport' => true,]]);
$server->listen($socket);

$loop->run();