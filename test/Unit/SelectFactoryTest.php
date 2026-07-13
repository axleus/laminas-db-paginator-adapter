<?php

declare(strict_types=1);

namespace PhpDbTest\Paginator\Adapter\Unit;

use PhpDb\Paginator\Adapter\Exception\InvalidArgumentException;
use PhpDb\Paginator\Adapter\Select;
use PhpDb\Paginator\Adapter\SelectFactory;
use PhpDb\Sql;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[Group('unit')]
final class SelectFactoryTest extends TestCase
{
    public function testThrowsWhenOptionsAreMissing(): void
    {
        $factory = new SelectFactory();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing options');
        $factory($this->createMock(ContainerInterface::class), Select::class);
    }

    public function testReturnsSelectBuiltFromOptions(): void
    {
        $factory = new SelectFactory();
        $select  = $this->createMock(Sql\Select::class);
        $sql     = $this->createMock(Sql\Sql::class);

        $this->assertInstanceOf(
            Select::class,
            $factory($this->createMock(ContainerInterface::class), Select::class, [$select, $sql])
        );
    }
}
