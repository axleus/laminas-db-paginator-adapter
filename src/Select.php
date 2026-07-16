<?php

declare(strict_types=1);

namespace PhpDb\Paginator\Adapter;

use Laminas\Paginator\Adapter\AdapterInterface;
use Override;
use PhpDb\Adapter\AdapterInterface as DbAdapterInterface;
use PhpDb\Paginator\Adapter\Exception\MissingRowCountColumnException;
use PhpDb\Paginator\Adapter\Exception\UnexpectedValueException;
use PhpDb\ResultSet\ResultSet;
use PhpDb\ResultSet\ResultSetInterface;
use PhpDb\Sql;

use function array_key_exists;
use function is_array;
use function iterator_to_array;
use function strtolower;

/**
 * @implements AdapterInterface<array-key, mixed>
 */
class Select implements AdapterInterface
{
    protected Sql\Sql $sql;

    /**
     * Database query
     */
    protected Sql\Select $select;

    /**
     * Database count query
     */
    protected ?Sql\Select $countSelect;

    protected ResultSetInterface $resultSetPrototype;

    public const ROW_COUNT_COLUMN_NAME = 'C';

    /**
     * Total item count
     */
    protected int $rowCount = 0;

    /**
     * Constructs instance.
     *
     * @param Sql\Select                 $select             The select query
     * @param DbAdapterInterface|Sql\Sql $adapterOrSqlObject DB adapter or Sql\Sql object
     */
    public function __construct(
        Sql\Select $select,
        Sql\Sql|DbAdapterInterface $adapterOrSqlObject,
        ?ResultSetInterface $resultSetPrototype = null,
        ?Sql\Select $countSelect = null,
    ) {
        $this->select      = $select;
        $this->countSelect = $countSelect;

        if ($adapterOrSqlObject instanceof DbAdapterInterface) {
            $adapterOrSqlObject = new Sql\Sql($adapterOrSqlObject);
        }

        $this->sql                = $adapterOrSqlObject;
        $this->resultSetPrototype = $resultSetPrototype ?? new ResultSet();
    }

    /**
     * Returns the total number of rows in the result set.
     *
     * @throws UnexpectedValueException
     */
    #[Override]
    public function count(): int
    {
        $select    = $this->getSelectCount();
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (null === $result) {
            throw new UnexpectedValueException('Statement execution did not produce a result set');
        }

        /** @var array<string, mixed>|false|null $row */
        $row = $result->current();
        if (! is_array($row)) {
            throw MissingRowCountColumnException::forColumn(self::ROW_COUNT_COLUMN_NAME);
        }
        $this->rowCount = $this->locateRowCount($row);

        return $this->rowCount;
    }

    /**
     * Returns an array of items for a page.
     * Executes the {$itemsCallback}.
     *
     * @inheritDoc
     * @throws UnexpectedValueException
     */
    #[Override]
    public function getItems(int $offset, int $itemCountPerPage): array
    {
        $select = clone $this->select;
        $select->offset($offset)
            ->limit($itemCountPerPage);

        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (null === $result) {
            throw new UnexpectedValueException('Statement execution did not produce a result set');
        }

        $resultSet = clone $this->resultSetPrototype;
        $resultSet->initialize($result);

        return iterator_to_array($resultSet);
    }

    /**
     * Returns select query for count
     */
    protected function getSelectCount(): Sql\Select
    {
        if (null !== $this->countSelect) {
            return $this->countSelect;
        }

        $select = clone $this->select;
        $select->reset(Sql\Select::LIMIT);
        $select->reset(Sql\Select::OFFSET);
        $select->reset(Sql\Select::ORDER);

        $countSelect = new Sql\Select();

        $countSelect->columns([self::ROW_COUNT_COLUMN_NAME => new Sql\Expression('COUNT(1)')]);
        $countSelect->from(['original_select' => $select]);

        return $countSelect;
    }

    /**
     * @param array<string, mixed> $row
     * @throws MissingRowCountColumnException
     */
    private function locateRowCount(array $row): int
    {
        if (array_key_exists(self::ROW_COUNT_COLUMN_NAME, $row)) {
            return (int) $row[self::ROW_COUNT_COLUMN_NAME];
        }

        $lowerCaseColumnName = strtolower(self::ROW_COUNT_COLUMN_NAME);
        if (array_key_exists($lowerCaseColumnName, $row)) {
            return (int) $row[$lowerCaseColumnName];
        }

        throw MissingRowCountColumnException::forColumn(self::ROW_COUNT_COLUMN_NAME);
    }
}
