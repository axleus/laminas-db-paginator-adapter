<?php

declare(strict_types=1);

namespace PhpDbTest\Paginator\Adapter;

use PhpDb\Adapter\Adapter;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Driver\ResultInterface;
use PhpDb\Adapter\Driver\StatementInterface;
use PhpDb\Adapter\Platform\Sql92;
use PhpDb\Paginator\Adapter\Exception\UnexpectedValueException;
use PhpDb\Paginator\Adapter\Select;
use PhpDb\Paginator\Adapter\TableGateway;
use PhpDb\TableGateway\TableGateway as BaseTableGateway;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DbTableGatewayTest extends TestCase
{
    protected StatementInterface&MockObject $mockStatement;

    protected TableGateway $dbTableGateway;

    protected BaseTableGateway $tableGateway;

    public function setUp(): void
    {
        $mockStatement = $this->createMock(StatementInterface::class);
        $mockDriver    = $this->createMock(DriverInterface::class);
        $mockDriver
            ->expects($this->any())
            ->method('createStatement')
            ->willReturn($mockStatement);
        $mockDriver
            ->expects($this->any())
            ->method('formatParameterName')
            ->willReturnArgument(0);

        $adapter = new Adapter($mockDriver, new Sql92());

        $this->mockStatement = $mockStatement;
        $this->tableGateway  = new BaseTableGateway('foobar', $adapter);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testGetItems(): void
    {
        $this->dbTableGateway = new TableGateway($this->tableGateway);

        $mockResult = $this->createMock(ResultInterface::class);
        $this->mockStatement
            ->expects($this->any())
            ->method('execute')
            ->willReturn($mockResult);

        $items = $this->dbTableGateway->getItems(2, 10);
        $this->assertEquals([], $items);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testCount(): void
    {
        $this->dbTableGateway = new TableGateway($this->tableGateway);

        $mockResult = $this->createMock(ResultInterface::class);
        $mockResult
            ->expects($this->any())
            ->method('current')
            ->willReturn([Select::ROW_COUNT_COLUMN_NAME => 10]);

        $this->mockStatement
            ->expects($this->any())
            ->method('execute')
            ->willReturn($mockResult);

        $count = $this->dbTableGateway->count();
        $this->assertEquals(10, $count);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testGetItemsWithWhereAndOrder(): void
    {
        $where                = "foo = bar";
        $order                = "foo";
        $this->dbTableGateway = new TableGateway($this->tableGateway, $where, $order);

        $mockResult = $this->createMock(ResultInterface::class);
        $this->mockStatement
            ->expects($this->any())
            ->method('execute')
            ->willReturn($mockResult);

        $items = $this->dbTableGateway->getItems(2, 10);
        $this->assertEquals([], $items);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testGetItemsWithWhereAndOrderAndGroup(): void
    {
        $where                = "foo = bar";
        $order                = "foo";
        $group                = "foo";
        $this->dbTableGateway = new TableGateway($this->tableGateway, $where, $order, $group);

        $mockResult = $this->createMock(ResultInterface::class);
        $this->mockStatement
            ->expects($this->once())
            ->method('setSql')
            ->with(
                $this->equalTo(
                    'SELECT "foobar".* FROM "foobar"'
                          . ' WHERE foo = bar GROUP BY "foo" ORDER BY "foo" ASC LIMIT limit OFFSET offset'
                )
            );
        $this->mockStatement
            ->expects($this->any())
            ->method('execute')
            ->willReturn($mockResult);

        $items = $this->dbTableGateway->getItems(2, 10);
        $this->assertEquals([], $items);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function testGetItemsWithWhereAndOrderAndGroupAndHaving(): void
    {
        $where                = "foo = bar";
        $order                = "foo";
        $group                = "foo";
        $having               = "count(foo)>0";
        $this->dbTableGateway = new TableGateway($this->tableGateway, $where, $order, $group, $having);

        $mockResult = $this->createMock(ResultInterface::class);
        $this->mockStatement
            ->expects($this->once())
            ->method('setSql')
            ->with(
                $this->equalTo(
                    'SELECT "foobar".* FROM "foobar" WHERE foo = bar GROUP BY "foo" HAVING count(foo)>0 '
                    . 'ORDER BY "foo" ASC LIMIT limit OFFSET offset'
                )
            );
        $this->mockStatement
            ->expects($this->any())
            ->method('execute')
            ->willReturn($mockResult);

        $items = $this->dbTableGateway->getItems(2, 10);
        $this->assertEquals([], $items);
    }
}
