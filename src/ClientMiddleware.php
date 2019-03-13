<?php declare(strict_types=1);

namespace Jasny\Dummy;

use Http\Client\Common\Plugin as HttpPlugin;
use Http\Promise\Promise as HttpPromise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Dummy middleware for PSR-7 HTTP clients.
 */
class ClientMiddleware
{
    /**
     * Return a callback that can be used as double pass middleware.
     *
     * @return callable
     */
    public function asDoublePass(): callable
    {
        return function (RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface {
            return $next($request, $response);
        };
    }

    /**
     * Return a callback that can be used as Guzzle middleware.
     * @see http://docs.guzzlephp.org/en/stable/handlers-and-middleware.html
     *
     * @return callable
     */
    public function forGuzzle(): callable
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                return $handler($request, $options);
            };
        };
    }

    /**
     * Create a version of this middleware that can be used in HTTPlug.
     * @see http://docs.php-http.org/en/latest/plugins/introduction.html
     *
     * @return self&HttpPlugin
     */
    public function forHttplug(): HttpPlugin
    {
        return new class () extends ClientMiddleware implements HttpPlugin {
            public function handleRequest(RequestInterface $request, callable $next, callable $first): HttpPromise
            {
                return $next($request);
            }
        };
    }
}
