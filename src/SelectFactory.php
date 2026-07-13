<?php

declare(strict_types=1);

namespace PhpDb\Paginator\Adapter;

use PhpDb\Adapter\AdapterInterface as DbAdapterInterface;
use PhpDb\Paginator\Adapter\Exception\InvalidArgumentException;
use PhpDb\ResultSet\ResultSetInterface;
use PhpDb\Sql;
use Psr\Container\ContainerInterface;

class SelectFactory
{
    /**
     * @param class-string $requestedName
     * @param array|null $options
     * @psalm-param array{
     *     0: Sql\Select,
     *     1: Sql\Sql|DbAdapterInterface,
     *     2?: ResultSetInterface|null,
     *     3?: Sql\Select|null,
     * }|null $options
     * @throws InvalidArgumentException
     */
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): Select
    {
        if ($options === null) {
            throw new InvalidArgumentException('Missing options');
        }

        return new Select(...$options);
    }
}
