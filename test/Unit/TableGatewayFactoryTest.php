<?php

declare(strict_types=1);

namespace PhpDbTest\Paginator\Adapter\Unit;

use PhpDb\Adapter\Adapter;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Platform\Sql92;
use PhpDb\Paginator\Adapter\Exception\InvalidArgumentException;
use PhpDb\Paginator\Adapter\TableGateway;
use PhpDb\Paginator\Adapter\TableGatewayFactory;
use PhpDb\TableGateway\TableGateway as BaseTableGateway;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[Group('unit')]
final class TableGatewayFactoryTest extends TestCase
{
    public function testThrowsWhenOptionsAreMissing(): void
    {
        $factory = new TableGatewayFactory();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing options');
        $factory($this->createMock(ContainerInterface::class), TableGateway::class);
    }

    public function testReturnsTableGatewayBuiltFromOptions(): void
    {
        $adapter      = new Adapter($this->createMock(DriverInterface::class), new Sql92());
        $tableGateway = new BaseTableGateway('foobar', $adapter);
        $factory      = new TableGatewayFactory();

        $this->assertInstanceOf(
            TableGateway::class,
            $factory($this->createMock(ContainerInterface::class), TableGateway::class, [$tableGateway])
        );
    }
}
