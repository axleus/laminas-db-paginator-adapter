<?php

declare(strict_types=1);

namespace PhpDb\Paginator\Adapter;

use Closure;
use PhpDb\Sql\Having;
use PhpDb\Sql\Where;
use PhpDb\TableGateway\AbstractTableGateway;

final class TableGateway extends Select
{
    /**
     * Constructs instance.
     *
     * @param Where|array<array-key, mixed>|Closure|string|null  $where
     * @param array<array-key, mixed>|string|null                $order
     * @param array<array-key, mixed>|string|null                $group
     * @param Having|array<array-key, mixed>|Closure|string|null $having
     */
    public function __construct(
        AbstractTableGateway $tableGateway,
        Where|array|Closure|string|null $where = null,
        array|string|null $order = null,
        array|string|null $group = null,
        Having|array|Closure|string|null $having = null,
    ) {
        $this->sql    = $tableGateway->getSql();
        $this->select = $this->sql->select();

        if (null !== $where) {
            $this->select->where($where);
        }

        if (null !== $order) {
            $this->select->order($order);
        }

        if (null !== $group) {
            $this->select->group($group);
        }

        if (null !== $having) {
            $this->select->having($having);
        }

        $this->resultSetPrototype = $tableGateway->getResultSetPrototype();
        $this->countSelect        = null;

        parent::__construct(
            $this->select,
            $tableGateway->getAdapter(),
            $this->resultSetPrototype,
            $this->countSelect,
        );
    }
}
