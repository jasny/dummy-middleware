<?php

namespace Jasny\HttpDummy\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler as GuzzleMockHandler;
use GuzzleHttp\HandlerStack as GuzzleHandlerStack;
use GuzzleHttp\Middleware as GuzzleMiddleware;
use GuzzleHttp\Promise\Promise as GuzzlePromise;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Http\Mock\Client as HttpMockClient;
use Http\Client\Common\PluginClient as HttpPluginClient;
use Jasny\HttpDummy\ClientMiddleware;
use Jasny\TestHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @covers \Jasny\HttpDummy\ClientMiddleware
 */
class ClientMiddlewareTest extends TestCase
{
    use TestHelper;

    /**
     * @var ClientMiddleware
     */
    protected $middleware;

    public function setUp()
    {
        $this->middleware = new ClientMiddleware();
    }

    public function testAsDoublePassMiddleware()
    {
        $inputRequest = $this->createMock(RequestInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $next = $this->createCallbackMock($this->once(), [$this->identicalTo($inputRequest)], $response);

        $doublePass = $this->middleware->asDoublePass();
        $ret = $doublePass($inputRequest, $response, $next);

        $this->assertSame($response, $ret);
    }

    public function testAsGuzzleMiddlewareWithSyncRequest()
    {
        $response = $this->createMock(ResponseInterface::class);
        $history = [];

        $mockHandler = new GuzzleMockHandler([$response]);
        $handlerStack = GuzzleHandlerStack::create($mockHandler);

        $handlerStack->push($this->middleware->forGuzzle());
        $handlerStack->push(GuzzleMiddleware::history($history));

        $client = new GuzzleClient(['handler' => $handlerStack]);

        $options = ['timeout' => 90, 'answer' => 42, 'body' => 'test'];

        $ret = $client->request('GET', '/foo', $options);

        $this->assertSame($response, $ret);

        $this->assertCount(1, $history);
        $this->assertInstanceOf(GuzzleRequest::class, $history[0]['request']);
        $this->assertSame($response, $history[0]['response']);

        $expectedOptions = ['timeout' => 90, 'answer' => 42, 'handler' => $handlerStack];
        $actualOptions = array_intersect_key($history[0]['options'], $expectedOptions);
        $this->assertSame($expectedOptions, $actualOptions);
    }

    public function testAsGuzzleMiddlewareWithAsyncRequest()
    {
        $response = $this->createMock(ResponseInterface::class);
        $history = [];

        $mockHandler = new GuzzleMockHandler([$response]);
        $handlerStack = GuzzleHandlerStack::create($mockHandler);

        $handlerStack->push($this->middleware->forGuzzle());
        $handlerStack->push(GuzzleMiddleware::history($history));

        $client = new GuzzleClient(['handler' => $handlerStack]);

        $options = ['timeout' => 90, 'answer' => 42, 'body' => 'test'];

        $ret = $client->requestAsync('GET', '/foo', $options);

        $this->assertInstanceOf(GuzzlePromise::class, $ret);
        $this->assertSame($response, $ret->wait());

        $this->assertCount(1, $history);
        $this->assertInstanceOf(GuzzleRequest::class, $history[0]['request']);
        $this->assertSame($response, $history[0]['response']);

        $expectedOptions = ['timeout' => 90, 'answer' => 42, 'handler' => $handlerStack];
        $actualOptions = array_intersect_key($history[0]['options'], $expectedOptions);
        $this->assertSame($expectedOptions, $actualOptions);
    }

    public function testAsHttplugMiddleware()
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $mockClient = new HttpMockClient();
        $mockClient->setDefaultResponse($response);

        $client = new HttpPluginClient($mockClient, [$this->middleware->forHttplug()]);

        $ret = $client->sendRequest($request);

        $this->assertSame($response, $ret);
    }
}
