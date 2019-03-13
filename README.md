Jasny Dummy Middleware
===

[![Build Status](https://travis-ci.org/jasny/dummy-middleware.svg?branch=master)](https://travis-ci.org/jasny/dummy-middleware)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jasny/dummy-middleware/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jasny/dummy-middleware/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/jasny/dummy-middleware/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jasny/dummy-middleware/?branch=master)
[![Packagist Stable Version](https://img.shields.io/packagist/v/jasny/dummy-middleware.svg)](https://packagist.org/packages/jasny/dummy-middleware)
[![Packagist License](https://img.shields.io/packagist/l/jasny/dummy-middleware.svg)](https://packagist.org/packages/jasny/dummy-middleware)

Dummy client and server middleware for PSR-7 requests. Works both as PSR-15 and double pass middleware.

The dummy services work as [null objects](https://sourcemaking.com/design_patterns/null_object) / passthrough,
preventing the use of if's.

Installation
---

    composer require jasny/dummy-middleware

Usage
---

### Server middleware

Server middleware can be used all passthrough of PSR-7 server requests.

#### Single pass middleware (PSR-15)

The middleware implements the PSR-15 `MiddlewareInterface`. As PSR standard many new libraries support this type of
middleware, for example [Zend Stratigility](https://docs.zendframework.com/zend-stratigility/). 

```php
use Jasny\Dummy\ServerMiddleware;
use Zend\Stratigility\MiddlewarePipe;
use Zend\Diactoros\ResponseFactory;

$middleware = new ServerMiddleware();

$app = new MiddlewarePipe();
$app->pipe($middleware);
```

#### Double pass middleware

Many PHP libraries support double pass middleware. These are callables with the following signature;

```php
fn(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
```

To get a callback to be used by libraries as [Jasny Router](https://github.com/jasny/router) and
[Relay](http://relayphp.com/), use the `asDoublePass()` method.

```php
use Jasny\Dummy\ServerMiddleware;
use Relay\RelayBuilder;

$middleware = new ServerMiddleware();

$relayBuilder = new RelayBuilder($resolver);
$relay = $relayBuilder->newInstance([
    $middleware->asDoublePass(),
]);

$response = $relay($request, $baseResponse);
```

### Client middleware

Client middleware can be used for PSR-7 compatible HTTP clients like [Guzzle](http://docs.guzzlephp.org) and
[HTTPlug](http://docs.php-http.org).

#### Double pass middleware

The client middleware can be used by any client that does support double pass middleware. Such middleware are callables
with the following signature;

```php
fn(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
```

Most HTTP clients do not support double pass middleware, but a type of single pass instead. However more general
purpose PSR-7 middleware libraries, like [Relay](http://relayphp.com/), do support double pass.

```php
use Relay\RelayBuilder;
use Jasny\Dummy\ClientMiddleware;

$middleware = new ClientMiddleware();

$relayBuilder = new RelayBuilder($resolver);
$relay = $relayBuilder->newInstance([
    $middleware->asDoublePass(),
]);

$response = $relay($request, $baseResponse);
```

_The client middleware does not conform to PSR-15 (single pass) as that is intended for server requests only._

#### Guzzle

[Guzzle](http://docs.guzzlephp.org) is the most popular HTTP Client for PHP. The middleware has a `forGuzzle()` method
that creates a callback which can be used as Guzzle middleware.

```php
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client;
use Jasny\Dummy\ClientMiddleware;

$middleware = new ClientMiddleware();

$stack = new HandlerStack();
$stack->push($middleware->forGuzzle());

$client = new Client(['handler' => $stack]);
```

#### HTTPlug

[HTTPlug](http://docs.php-http.org/en/latest/httplug/introduction.html) is the HTTP client of PHP-HTTP. It allows you
to write reusable libraries and applications that need an HTTP client without binding to a specific implementation.

The `forHttplug()` method for the middleware creates an object that can be used as HTTPlug plugin.

```php
use Http\Discovery\HttpClientDiscovery;
use Http\Client\Common\PluginClient;
use Jasny\Dummy\ClientMiddleware;

$middleware = new ClientMiddleware();

$pluginClient = new PluginClient(
    HttpClientDiscovery::find(),
    [
        $middleware->forHttplug(),
    ]
);
```

