<?php declare(strict_types=1);

namespace Jasny\Dummy;

use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Dummy middleware when handling PSR-7 server requests.
 * Can be used both as single pass (PSR-15) and double pass middleware.
 */
class ServerMiddleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request (PSR-15).
     *
     * @param ServerRequest  $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function process(ServerRequest $request, RequestHandler $handler): Response
    {
        return $handler->handle($request);
    }

    /**
     * Get a callback that can be used as double pass middleware.
     *
     * @return callable
     */
    public function asDoublePass(): callable
    {
        return function (ServerRequest $request, Response $response, callable $next): Response {
            return $next($request, $response);
        };
    }
}
