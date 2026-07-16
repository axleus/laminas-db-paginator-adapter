<?php

declare(strict_types=1);

namespace PhpDbTest\Paginator\Adapter\Integration;

use PhpDb\Paginator\Adapter\Exception\UnexpectedValueException;
use PhpDb\Paginator\Adapter\Select;
use PhpDb\Sql\Expression;
use PhpDb\Sql\Select as SqlSelect;
use PhpDbTest\Paginator\Adapter\Trait\UsesSqliteDatabaseTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
final class SelectTest extends TestCase
{
    use UsesSqliteDatabaseTrait;

    /**
     * @throws UnexpectedValueException
     */
    #[Test]
    public function countBuildsAndExecutesWrappingCountQuery(): void
    {
        $select = new SqlSelect('posts');
        $select->order('id ASC');

        $adapter = new Select($select, $this->adapter);

        static::assertSame(5, $adapter->count());
    }

    /**
     * @throws UnexpectedValueException
     */
    #[Test]
    public function countUsesProvidedCountSelect(): void
    {
        $countSelect = new SqlSelect('posts');
        $countSelect->columns([Select::ROW_COUNT_COLUMN_NAME => new Expression('COUNT(1)')]);
        $countSelect->where('id <= 2');

        $adapter = new Select(new SqlSelect('posts'), $this->adapter, null, $countSelect);

        static::assertSame(2, $adapter->count());
    }

    /**
     * @throws UnexpectedValueException
     */
    #[Test]
    public function getItemsPastEndOfDataReturnsEmptyArray(): void
    {
        $select = new SqlSelect('posts');
        $select->order('id ASC');

        $adapter = new Select($select, $this->adapter);

        static::assertSame([], $adapter->getItems(10, 2));
    }

    /**
     * @throws UnexpectedValueException
     */
    #[Test]
    public function getItemsReturnsRequestedPageOfRows(): void
    {
        $select = new SqlSelect('posts');
        $select->order('id ASC');

        $adapter = new Select($select, $this->adapter);

        static::assertSame(['post-3', 'post-4'], $this->titlesOf($adapter->getItems(2, 2)));
    }

    public function setUp(): void
    {
        $this->setUpDatabase();
        $this->seedPosts(['post-1', 'post-2', 'post-3', 'post-4', 'post-5']);
    }
}
