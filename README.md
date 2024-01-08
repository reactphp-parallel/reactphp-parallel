# ReactPHP bindings around ext-parallel

![Continuous Integration](https://github.com/reactphp-parallel/reactphp-parallel/workflows/Continuous%20Integration/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/react-parallel/react-parallel/v/stable.png)](https://packagist.org/packages/react-parallel/react-parallel)
[![Total Downloads](https://poser.pugx.org/react-parallel/react-parallel/downloads.png)](https://packagist.org/packages/react-parallel/react-parallel)
[![Code Coverage](https://scrutinizer-ci.com/g/reactphp-parallel/reactphp-parallel/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/reactphp-parallel/reactphp-parallel/?branch=master)
[![Type Coverage](https://shepherd.dev/github/reactphp-parallel/reactphp-parallel/coverage.svg)](https://shepherd.dev/github/reactphp-parallel/reactphp-parallel)
[![License](https://poser.pugx.org/react-parallel/react-parallel/license.png)](https://packagist.org/packages/react-parallel/react-parallel)

## Install ##

To install via [Composer](http://getcomposer.org/), use the command below, it will automatically detect the latest version and bind it with `~`.

```
composer require react-parallel/react-parallel
```

## Usage

The factory in this package provides the following methods for quick and shared access to low level components.

```php
$loop = EventLoopFactory::create();
$factory = new Factory($loop);
$factory->loop(); // Returns the event loop, mainly for convenience
$factory->eventLoopBridge(); // Returns the event loop bridge, used as a central place to translate channels and futures to observables and promises
$factory->streams(); // Returns the stream factory, which provides high level stream abstractions
$factory->call(function (int $time): int {
    return $time;
}, [time()]); // Executes the closure passed into this method in a thread and returns a promises for any results coming out of that closure
$factory->lowLevelPool(); // Returns a low level pool that will scale infinitely (as a long as you have resources enough to scale)
$factory->limitedPool(12); // Returns a limited pool with a maximum number of threads specified by you
```

## Metrics

This package supports metrics through [`wyrihaximus/metrics`](https://github.com/wyrihaximus/php-metrics):

```php
use React\EventLoop\Factory as EventLoopFactory;
use ReactParallel\Factory;
use ReactParallel\Metrics;
use WyriHaximus\Metrics\Configuration;
use WyriHaximus\Metrics\InMemory\Registry;

$loop = EventLoopFactory::create();
$registry = new Registry(Configuration::create());
$factory = (new Factory($loop))->withMetrics(Metrics::create($registry));
```

## License ##

Copyright 2024 [Cees-Jan Kiewiet](http://wyrihaximus.net/)

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
