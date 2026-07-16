<?php

declare(strict_types=1);

namespace PhpDbTest\Paginator\Adapter\Unit;

use PhpDb\Adapter\Adapter;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Driver\ResultInterface;
use PhpDb\Adapter\Driver\StatementInterface;
use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Paginator\Adapter\Exception\MissingRowCountColumnException;
use PhpDb\Paginator\Adapter\Exception\UnexpectedValueException;
use PhpDb\Paginator\Adapter\Select;
use PhpDb\Sql;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function strtolower;

#[Group('unit')]
final class DbSelectTest extends TestCase
{
    protected Sql\Select&MockObject $mockSelect;

    protected Sql\Select&MockObject $mockSelectCount;

    protected StatementInterface&MockObject $mockStatement;

    protected ResultInterface&MockObject $mockResult;

    protected Sql\Sql&MockObject $mockSql;

    protected Select $dbSelect;

    /**
     * @throws UnexpectedValueException
     */
    #[Test]
    public function countQueryWithLowerColumnNameShouldReturnValidResult(): void
    {
        $this->dbSelect = new Select($this->mockSelect, $this->mockSql);
        $this->mockResult
            ->expects($this->once())
            ->method('current')
            ->willReturn([strtolower(Select::ROW_COUNT_COLUMN_NAME) => 7]);

        $count = $this->dbSelect->count();
        static::assertSame(7, $count);
    }

    /**
     * @throws UnexpectedValueException
     */
    #[Test]
    public function countQueryWithMissingColumnNameShouldRaiseException(): void
    {
        $this->dbSelect = new Select($this->mockSelect, $this->mockSql);
        $this->mockResult
            ->expects($this->once())
            ->method('current')
            ->willReturn([]);

        $this->expectException(MissingRowCountColumnException::class);
        $this->dbSelect->count();
    }

    /**
     * @throws UnexpectedValueException
     */
    #[Test]
    public function countReturnsTotalRowCount(): void
    {
        $this->mockResult
            ->expects($this->once())
            ->method('current')
            ->willReturn([Select::ROW_COUNT_COLUMN_NAME => 5]);

        $this->mockSelect->expects($this->exactly(3))->method('reset'); // called for columns, limit, offset, order

        $count = $this->dbSelect->count();
        static::assertSame(5, $count);
    }

    /**
     * @throws UnexpectedValueException
     */
    #[Test]
    public function countThrowsWhenResultRowIsNotArray(): void
    {
        $this->mockResult
            ->expects($this->once())
            ->method('current')
            ->willReturn(false);

        $this->expectException(MissingRowCountColumnException::class);
        $this->expectExceptionMessage('missing row count column');
        $this->dbSelect->count();
    }

    /**
     * @throws UnexpectedValueException
     */
    #[Test]
    public function countThrowsWhenStatementProducesNoResult(): void
    {
        $statement = $this->createMock(StatementInterface::class);
        $statement->expects($this->once())->method('execute')->willReturn(null);

        $dbSelect = new Select($this->mockSelect, $this->createMockSql($statement));

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Statement execution did not produce a result set');
        $dbSelect->count();
    }

    /**
     * @throws UnexpectedValueException
     */
    #[Test]
    public function customCount(): void
    {
        $this->dbSelect = new Select($this->mockSelect, $this->mockSql, null, $this->mockSelectCount);
        $this->mockResult
            ->expects($this->once())
            ->method('current')
            ->willReturn([Select::ROW_COUNT_COLUMN_NAME => 7]);

        $count = $this->dbSelect->count();
        static::assertSame(7, $count);
    }

    #[Test]
    public function getArrayCopyShouldContainSelectItems(): void
    {
        $this->dbSelect = new Select(
            $this->mockSelect,
            $this->mockSql,
            null,
            $this->mockSelectCount,
        );
        static::assertSame(
            [
                'select',
                'count_select',
            ],
            array_keys($this->dbSelect->getArrayCopy()),
        );
    }

    /**
     * @throws UnexpectedValueException
     */
    #[Test]
    public function getItems(): void
    {
        $this->mockSelect
            ->expects($this->once())
            ->method('limit')
            ->with(static::equalTo(10))
            ->willReturnSelf();

        $this->mockSelect
            ->expects($this->once())
            ->method('offset')
            ->with(static::equalTo(2))
            ->willReturnSelf();

        $items = $this->dbSelect->getItems(2, 10);
        static::assertEquals([], $items);
    }

    /**
     * @throws UnexpectedValueException
     */
    #[Test]
    public function getItemsThrowsWhenStatementProducesNoResult(): void
    {
        $statement = $this->createMock(StatementInterface::class);
        $statement->expects($this->once())->method('execute')->willReturn(null);

        $this->mockSelect->method('offset')->willReturnSelf();
        $this->mockSelect->method('limit')->willReturnSelf();

        $dbSelect = new Select($this->mockSelect, $this->createMockSql($statement));

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Statement execution did not produce a result set');
        $dbSelect->getItems(0, 10);
    }

    /**
     * @throws UnexpectedValueException
     */
    #[Test]
    #[Group('6817')]
    #[Group('6812')]
    public function returnValueIsArray(): void
    {
        $this->mockSelect
            ->expects($this->once())
            ->method('limit')
            ->with(static::equalTo(10))
            ->willReturnSelf();

        $this->mockSelect
            ->expects($this->once())
            ->method('offset')
            ->with(static::equalTo(0))
            ->willReturnSelf();

        static::assertIsArray($this->dbSelect->getItems(0, 10));
    }

    public function setUp(): void
    {
        $this->mockResult    = $this->createMock(ResultInterface::class);
        $this->mockStatement = $this->createMock(StatementInterface::class);

        $this->mockStatement->expects($this->any())->method('execute')->willReturn($this->mockResult);

        $this->mockSql = $this->createMockSql($this->mockStatement);

        $this->mockSelect      = $this->createMock(Sql\Select::class);
        $this->mockSelectCount = $this->createMock(Sql\Select::class);
        $this->dbSelect        = new Select($this->mockSelect, $this->mockSql);
    }

    private function createMockSql(StatementInterface&MockObject $statement): Sql\Sql&MockObject
    {
        $mockDriver   = $this->createMock(DriverInterface::class);
        $mockPlatform = $this->createMock(PlatformInterface::class);

        $mockDriver->expects($this->any())->method('createStatement')->willReturn($statement);
        $mockPlatform->expects($this->any())->method('getName')->willReturn('platform');

        $mockSql = $this->getMockBuilder(Sql\Sql::class)
            ->setConstructorArgs(
                [
                    $this->getMockBuilder(Adapter::class)
                        ->setConstructorArgs([$mockDriver, $mockPlatform])
                        ->getMock(),
                ],
            )
            ->getMock();

        $mockSql->expects($this->any())
            ->method('prepareStatementForSqlObject')
            ->with($this->isInstanceOf(Sql\Select::class))
            ->willReturn($statement);

        return $mockSql;
    }
}
