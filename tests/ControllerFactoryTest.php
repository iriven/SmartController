<?php

namespace Xocotlah\SmartController\Tests\Feature;

use League\Container\Container;
use League\Route\RouteCollection;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;
use Xocotlah\SmartController\ControllerFactory;
use Xocotlah\SmartController\Tests\Asset\ClassController;
use Xocotlah\SmartController\Tests\Asset\ExtendsSmartController;
use Xocotlah\SmartController\SmartControllerInterface;

class ControllerFactoryTest extends TestCase
{
    protected $container;

    protected $factory;

    public function setUp()
    {
        $this->container = new Container;
        $this->factory = new ControllerFactory;
        $this->container->delegate($this->factory);

        $this->container->add(ServerRequestInterface::class, $this->createMock(ServerRequestInterface::class));
        $this->container->add(ResponseInterface::class, $this->createMock(ResponseInterface::class));
        $this->container->add(RouteCollection::class, $this->createMock(RouteCollection::class));
    }

    /**
     * @dataProvider classesProvider
     */
    public function test_has($prefix, $class, $shouldHave)
    {
        $this->factory->setNamespacePrefix($prefix);
        $this->assertEquals($shouldHave, $this->container->has($class));
    }

    /**
     * @dataProvider classesProvider
     */
    public function test_get($prefix, $class, $shouldHave)
    {
        $this->factory->setNamespacePrefix($prefix);
        if (!$shouldHave) {
            $this->expectException(NotFoundExceptionInterface::class);
        }

        $controller = $this->container->get($class);

        if ($shouldHave) {
            $this->assertInstanceOf(SmartControllerInterface::class, $controller);
        }
    }

    /**
     * @dataProvider classesProvider
     */
    public function test_has_then_get($prefix, $class, $shouldHave)
    {
        $this->factory->setNamespacePrefix($prefix);
        $this->assertEquals($shouldHave, $this->factory->has($class));
        if ($shouldHave) {
            $this->assertInstanceOf(
                SmartControllerInterface::class,
                $this->container->get($class)
            );
        }
    }

    public function classesProvider()
    {
        return [
            ['', 'ExtendsSmartController', false],
            ['', 'Xocotlah\\SmartController\\Tests\\Asset\\ExtendsSmartController', true],
            ['Xocotlah\\SmartController\\Tests\\Asset\\', 'ExtendsSmartController', true],
            ['Foo\\', 'InexistantController', false],
            ['Foo\\', 'Xocotlah\\SmartController\\Tests\\Asset\\ExtendsSmartController', true],
            ['', 'Xocotlah\\SmartController\\Tests\\Asset\\ClassController', false],
            ['', 'ClassController', false],
            ['Xocotlah\\SmartController\\Tests\\Asset\\', 'ClassController', false],
            ['Foo\\', 'Xocotlah\\SmartController\\Tests\\Asset\\ClassController', false],
        ];
    }

    /**
     * @dataProvider notFoundClassesProvider
     */
    public function test_throw_an_exception_when_has_not($prefix, $class)
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->factory->setNamespacePrefix($prefix);
        $this->factory->get($class);
    }

    public function notFoundClassesProvider()
    {
        return [
            ['', 'ExtendsSmartController'],
            ['Foo\\', 'InexistantController'],
            ['', 'Xocotlah\\SmartController\\Tests\\Asset\\ClassController'],
            ['', 'ClassController'],
            ['Xocotlah\\SmartController\\Tests\\Asset\\', 'ClassController'],
            ['Foo\\', 'Xocotlah\\SmartController\\Tests\\Asset\\ClassController'],
        ];
    }

}
