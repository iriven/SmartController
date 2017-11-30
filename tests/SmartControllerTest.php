<?php

namespace Xocotlah\SmartController\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Xocotlah\SmartController\Tests\Asset\ExtendsSmartController;
use Xocotlah\SmartController\Strategy\SmartStrategy;
use Xocotlah\SmartController\SmartController;
use League\Route\Route;
use League\Route\RouteCollection;

class SmartControllerTest extends TestCase
{
    public function test_smart_controller()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $stream = $this->createMock(StreamInterface::class);
        $response->method('getBody')
            ->willReturn($stream);

        $uri = $this->createMock('Psr\Http\Message\UriInterface');
        $uri->method('getPath')->will($this->returnValue('/hello/world'));

        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($uri);

        $router = new \League\Route\RouteCollection;
        $router->setStrategy(new SmartStrategy);

        $controller = new ExtendsSmartController($request, $response, $router);

        $route = $router->get('/hello/{param}', [$controller, 'stringAction']);

        $return = $router->dispatch($request, $response);

        $params = ['param' => 'world'];

        // Do not reflect ExtendsSmartController class, since private router is defined on SmartController
        $ref = new \ReflectionClass(SmartController::class);
        foreach (['request', 'response', 'router', 'params'] as $prop) {
            $refProp = $ref->getProperty($prop);
            $refProp->setAccessible(true);
            $this->assertSame($$prop, $refProp->getValue($controller));
        }
    }


    /**
    * @dataProvider urlProvider
    */
    public function test_url($path, $params, $expected)
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $stream = $this->createMock(StreamInterface::class);

        $response->method('getBody')
            ->willReturn($stream);

        $router = $this->createMock(RouteCollection::class);

        $route = $this->createMock(Route::class);
        $route->expects($this->once())
            ->method('getPath')
            ->willReturn($path);

        $router->expects($this->once())
            ->method('getNamedRoute')
            ->with('testRoute')
            ->willReturn($route);

        $controller = new ExtendsSmartController($request, $response, $router);

        $router->expects($this->once())
            ->method('getNamedRoute')
            ->willReturn($route);

        $this->assertEquals($expected, $controller->url('testRoute', $params));
    }

    public function urlProvider()
    {
        return [
            ['/test', [], '/test'],
            ['/test/{param}', ['param' => 'something'], '/test/something'],
            ['/te{ param }st', ['param' => 42], '/te42st'],
            ['/test/{param1}/test2/{param2}', ['param1' => 'hello', 'param2' => 'world'],'/test/hello/test2/world'],
            ['/test/{param:\d+}', ['param' => 12345], '/test/12345'],
            ['/test/{ param : \d{1,9} }', ['param' => 12345], '/test/12345'],
            ['/test[opt]', [], '/test'],
            ['/test[/{param}]', ['param' => 'Foo'], '/test'],
            ['/{param}[opt]', ['param' => 'Foo'], '/Foo'],
            ['/test[/{name}[/{id:[0-9]+}]]', ['name' => 'Foo', 'id' => 42], '/test'],
            // We expect an empty string, because the path has not been parsed by the RouteParser
            ['[test]', [], ''],
            ['/{foo-bar}', ['foo-bar' => 'BazBaz'], '/BazBaz'],
            ['/{_foo:.*}', ['_foo' => 'BarBaz'], '/BarBaz'],
        ];
    }

    public function test_url_returns_route_name_on_failure()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $router = $this->createMock(RouteCollection::class);

        $router->expects($this->once())
            ->method('getNamedRoute')
            ->will($this->throwException(new \InvalidArgumentException));

        $controller = new ExtendsSmartController($request, $response, $router);

        $this->assertEquals('testRoute', $controller->url('testRoute', []));
    }
}
