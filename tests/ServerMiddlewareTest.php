<?php

namespace Jasny\HttpDummy\Tests;

use Jasny\TestHelper;
use Jasny\HttpDummy\ServerMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @covers \Jasny\HttpDummy\ServerMiddleware
 */
class ServerMiddlewareTest extends TestCase
{
    use TestHelper;

    /**
     * @var ServerMiddleware
     */
    protected $middleware;


    public function setUp()
    {
        $this->middleware = new ServerMiddleware();
    }

    public function testProcess()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')
            ->with($this->identicalTo($request))
            ->willReturn($response);

        $ret = $this->middleware->process($request, $handler);

        $this->assertSame($response, $ret);
    }

    public function testAsDoublePass()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $inputResponse = $this->createMock(ResponseInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $next = $this->createCallbackMock(
            $this->once(),
            [$this->identicalTo($request), $this->identicalTo($inputResponse)],
            $response
        );

        $doublePass = $this->middleware->asDoublePass();
        $ret = $doublePass($request, $inputResponse, $next);

        $this->assertSame($response, $ret);
    }
}
