<?php

namespace Xocotlah\SmartController\Tests;

use PHPUnit\Framework\TestCase;
use Xocotlah\SmartController\Tests\Asset\ClassController;
use Xocotlah\SmartController\Tests\Asset\ExtendsSmartController;
use Xocotlah\SmartController\Strategy\SmartStrategy;
use Xocotlah\SmartController\ControllerFactory;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use League\Route\RouteCollection;
use League\Container\Container;

class SmartStrategyTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function test_controller_returns_response_body_content($prefix, $class, $action, $expectedContent)
    {
        // Setup Request and Response
        $stream = $this->createMock(StreamInterface::class);

        $stream->expects($this->once())
            ->method('write')
            ->with($expectedContent);

        $request  = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')
            ->willReturn($stream);

        $uri = $this->createMock('Psr\Http\Message\UriInterface');

        $uri->method('getPath')->will($this->returnValue('/example/world'));

        $request->method('getMethod')->will($this->returnValue('GET'));
        $request->method('getUri')->will($this->returnValue($uri));

        // Setup Container and ControllerFactory
        $container = new Container;

        $container->add(ServerRequestInterface::class, $request);
        $container->add(ResponseInterface::class, $response);
        $container->add(RouteCollection::class, function () use ($container) {
            return new RouteCollection($container);
        });

        $factory = new ControllerFactory;
        $factory->setNamespacePrefix($prefix);

        $container->delegate($factory);
        $router = $container->get(RouteCollection::class);

        $router->map('GET', '/example/{param}', [$class, $action])
            ->setStrategy(new SmartStrategy);

        // Dispatch
        $returnedResponse = $router->dispatch($request, $response);
    }

    public function dataProvider()
    {
        return [
            ['', ExtendsSmartController::class, 'stringAction', 'Hello world'],
            ['', ExtendsSmartController::class, 'arrayAction', '{"Hello":"world"}'],
            ['', ExtendsSmartController::class, 'stringableAction', 'Hello world'],
            ['', ClassController::class, 'stringAction', 'Hello world'],
            ['', ClassController::class, 'arrayAction', '{"Hello":"world"}'],
            ['', ClassController::class, 'stringableAction', 'Hello world'],
            ['Foo\\', ClassController::class, 'stringableAction', 'Hello world'],
        ];
    }
}
