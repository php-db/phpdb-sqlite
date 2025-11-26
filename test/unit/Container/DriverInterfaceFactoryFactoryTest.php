<?php

declare(strict_types=1);

namespace PhpDbTest\Adapter\Sqlite\Container;

use PhpDb\Adapter\Sqlite\Container\DriverInterfaceFactoryFactory;
use PhpDb\Adapter\Sqlite\Container\PdoDriverFactory;
use PhpDb\Adapter\Sqlite\Driver\Pdo\Pdo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RuntimeException;

#[CoversClass(DriverInterfaceFactoryFactory::class)]
final class DriverInterfaceFactoryFactoryTest extends TestCase
{
    public function testInvokeReturnsCallable(): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->method('get')->willReturnMap([
            [
                'config',
                [
                    'dependencies'        => [
                        'aliases'   => [
                            'sqlite' => Pdo::class,
                        ],
                        'factories' => [
                            Pdo::class => PdoDriverFactory::class,
                        ],
                    ],
                    'db'                  => [
                        'adapters' => [
                            'test_adapter' => [
                                'driver' => 'sqlite',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $factoryFactory = new DriverInterfaceFactoryFactory();
        $result         = $factoryFactory($containerMock, 'test_adapter');

        self::assertIsCallable($result);
    }

    public function testInvokeReturnsPdoDriverFactory(): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->method('get')->willReturnMap([
            [
                'config',
                [
                    'dependencies'        => [
                        'aliases'   => [
                            'sqlite' => Pdo::class,
                        ],
                        'factories' => [
                            Pdo::class => PdoDriverFactory::class,
                        ],
                    ],
                    'db'                  => [
                        'adapters' => [
                            'test_adapter' => [
                                'driver' => 'sqlite',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $factoryFactory = new DriverInterfaceFactoryFactory();
        $result         = $factoryFactory($containerMock, 'test_adapter');

        self::assertInstanceOf(PdoDriverFactory::class, $result);
    }

    public function testInvokeThrowsExceptionWhenDriverNotConfigured(): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->method('get')->willReturnMap([
            [
                'config',
                [
                    'db' => [
                        'adapters' => [
                            'test_adapter' => [],
                        ],
                    ],
                ],
            ],
        ]);

        $factoryFactory = new DriverInterfaceFactoryFactory();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Named adapter "test_adapter" is not configured with a driver');

        $factoryFactory($containerMock, 'test_adapter');
    }

    public function testInvokeWithDriverNotInAliases(): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->method('get')->willReturnMap([
            [
                'config',
                [
                    'dependencies'        => [
                        'aliases'   => [
                            'sqlite' => Pdo::class,
                        ],
                        'factories' => [
                            Pdo::class => PdoDriverFactory::class,
                        ],
                    ],
                    'db'                  => [
                        'adapters' => [
                            'test_adapter' => [
                                'driver' => Pdo::class,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $factoryFactory = new DriverInterfaceFactoryFactory();
        $result         = $factoryFactory($containerMock, 'test_adapter');

        self::assertInstanceOf(PdoDriverFactory::class, $result);
    }
}
