<?php

namespace Xocotlah\SmartController\Tests\Unit;

use PHPUnit\Framework\TestCase;
use League\Container\Container;
use League\Route\RouteCollection;
use Xocotlah\SmartController\ControllerFactory;
use Xocotlah\SmartController\SmartControllerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\NotFoundExceptionInterface;

class ControllerFactoryTest extends TestCase
{
    /**
     * @dataProvider classesProvider
     */
    public function test_get($prefix, $class, $shouldHave)
    {
        // $factory = new ControllerFactory;
        $factory = $this->getMockBuilder(ControllerFactory::class)
            ->setMethods(['reflectArguments'])
            ->getMock();

        $factory->method('reflectArguments')
            ->willReturn([
                $this->createMock(ServerRequestInterface::class),
                $this->createMock(ResponseInterface::class),
                $this->createMock(RouteCollection::class)]
            );
        $factory->setNamespacePrefix($prefix);

        if (!$shouldHave) {
            $this->expectException(NotFoundExceptionInterface::class);
        }

        $controller = $factory->get($class);

        if ($shouldHave) {
            $this->assertInstanceOf(SmartControllerInterface::class, $controller);
        }
    }

    public function classesProvider()
    {
        return [
            ['', 'Xocotlah\\SmartController\\Tests\\Asset\\ExtendsSmartController', true],
            ['Foo\\', 'Xocotlah\\SmartController\\Tests\\Asset\\ExtendsSmartController', true],
            ['Xocotlah\\SmartController\\Tests\\Asset\\', 'ExtendsSmartController', true],
            ['', 'ExtendsSmartController', false],
            ['', 'Xocotlah\\SmartController\\Tests\\Asset\\ClassController', false],
            ['Xocotlah\\SmartController\\Tests\\Asset\\', 'ClassControlelr', false],
            ['Foo\\', 'InexistantClass', false],
        ];
    }
}
