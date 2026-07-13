<?php

declare(strict_types=1);

namespace PhpDb\Paginator\Adapter;

use Closure;
use PhpDb\Paginator\Adapter\Exception\InvalidArgumentException;
use PhpDb\Sql\Having;
use PhpDb\Sql\Where;
use PhpDb\TableGateway\AbstractTableGateway;
use Psr\Container\ContainerInterface;

class TableGatewayFactory
{
    /**
     * @param class-string $requestedName
     * @param array|null $options
     * @psalm-param array{
     *     0: AbstractTableGateway,
     *     1?: Where|array|Closure|string|null,
     *     2?: array|string|null,
     *     3?: array|string|null,
     *     4?: Having|array|Closure|string|null,
     * }|null $options
     * @throws InvalidArgumentException
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): TableGateway {
        if ($options === null) {
            throw new InvalidArgumentException('Missing options');
        }

        return new TableGateway(...$options);
    }
}
