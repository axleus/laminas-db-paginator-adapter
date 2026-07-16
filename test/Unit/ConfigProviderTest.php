<?php

declare(strict_types=1);

namespace PhpDbTest\Paginator\Adapter\Unit;

use PhpDb\Paginator\Adapter\ConfigProvider;
use PhpDb\Paginator\Adapter\Select;
use PhpDb\Paginator\Adapter\SelectFactory;
use PhpDb\Paginator\Adapter\TableGateway;
use PhpDb\Paginator\Adapter\TableGatewayFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class ConfigProviderTest extends TestCase
{
    #[Test]
    public function invokeMapsAdapterAliasesAndFactories(): void
    {
        static::assertSame(
            [
                'paginators' => [
                    'aliases'   => [
                        'select'       => Select::class,
                        'Select'       => Select::class,
                        'tablegateway' => TableGateway::class,
                        'tableGateway' => TableGateway::class,
                        'TableGateway' => TableGateway::class,
                    ],
                    'factories' => [
                        Select::class       => SelectFactory::class,
                        TableGateway::class => TableGatewayFactory::class,
                    ],
                ],
            ],
            (new ConfigProvider())(),
        );
    }
}
