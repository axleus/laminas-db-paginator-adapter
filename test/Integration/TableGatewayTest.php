<?php

declare(strict_types=1);

namespace PhpDbTest\Paginator\Adapter\Integration;

use PhpDb\Adapter\Adapter;
use PhpDb\Paginator\Adapter\Exception\UnexpectedValueException;
use PhpDb\Paginator\Adapter\TableGateway;
use PhpDb\TableGateway\TableGateway as BaseTableGateway;
use PhpDbTest\Paginator\Adapter\Trait\UsesSqliteDatabaseTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
final class TableGatewayTest extends TestCase
{
    use UsesSqliteDatabaseTrait;

    private BaseTableGateway $tableGateway;

    /**
     * @throws UnexpectedValueException
     */
    #[Test]
    public function countReflectsWhereCondition(): void
    {
        $adapter = new TableGateway($this->tableGateway, ['id > ?' => 2]);

        static::assertSame(3, $adapter->count());
    }

    /**
     * @throws UnexpectedValueException
     */
    #[Test]
    public function getItemsAppliesGroupAndHavingToUnderlyingQuery(): void
    {
        $this->adapter->query(
            "INSERT INTO posts (title) VALUES ('post-5')",
            Adapter::QUERY_MODE_EXECUTE,
        );

        $adapter = new TableGateway($this->tableGateway, null, 'title ASC', 'title', 'COUNT(*) > 1');

        static::assertSame(['post-5'], $this->titlesOf($adapter->getItems(0, 10)));
    }

    /**
     * @throws UnexpectedValueException
     */
    #[Test]
    public function getItemsAppliesWhereAndOrderToUnderlyingQuery(): void
    {
        $adapter = new TableGateway($this->tableGateway, ['id > ?' => 2], 'id DESC');

        static::assertSame(['post-5', 'post-4'], $this->titlesOf($adapter->getItems(0, 2)));
    }

    public function setUp(): void
    {
        $this->setUpDatabase();
        $this->seedPosts(['post-1', 'post-2', 'post-3', 'post-4', 'post-5']);
        $this->tableGateway = new BaseTableGateway('posts', $this->adapter);
    }
}
