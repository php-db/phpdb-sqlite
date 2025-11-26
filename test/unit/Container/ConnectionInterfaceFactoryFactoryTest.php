<?php

declare(strict_types=1);

namespace PhpDbTest\Adapter\Sqlite\Container;

use PhpDb\Adapter\Sqlite\Container\ConnectionInterfaceFactoryFactory;
use PhpDb\Adapter\Sqlite\Container\PdoConnectionFactory;
use PhpDb\Adapter\Sqlite\Driver\Pdo\Pdo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RuntimeException;

#[CoversClass(ConnectionInterfaceFactoryFactory::class)]
final class ConnectionInterfaceFactoryFactoryTest extends TestCase
{
    public function testInvokeReturnsCallable(): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->method('get')->willReturnMap([
            [
                'config',
                [
                    'dependencies' => [
                        'aliases' => [
                            'sqlite' => Pdo::class,
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

        $factoryFactory = new ConnectionInterfaceFactoryFactory();
        $result         = $factoryFactory($containerMock, 'test_adapter');

        self::assertIsCallable($result);
    }

    public function testInvokeReturnsPdoConnectionFactory(): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->method('get')->willReturnMap([
            [
                'config',
                [
                    'dependencies' => [
                        'aliases' => [
                            'sqlite' => Pdo::class,
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

        $factoryFactory = new ConnectionInterfaceFactoryFactory();
        $result         = $factoryFactory($containerMock, 'test_adapter');

        self::assertInstanceOf(PdoConnectionFactory::class, $result);
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

        $factoryFactory = new ConnectionInterfaceFactoryFactory();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Named adapter "test_adapter" is not configured with a driver');

        $factoryFactory($containerMock, 'test_adapter');
    }

    public function testInvokeThrowsExceptionForUnknownDriver(): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->method('get')->willReturnMap([
            [
                'config',
                [
                    'dependencies' => [
                        'aliases' => [
                            'sqlite' => Pdo::class,
                        ],
                    ],
                    'db'                  => [
                        'adapters' => [
                            'test_adapter' => [
                                'driver' => 'unknown',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $factoryFactory = new ConnectionInterfaceFactoryFactory();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No connection factory found for driver "unknown"');

        $factoryFactory($containerMock, 'test_adapter');
    }
}
