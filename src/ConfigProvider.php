<?php

declare(strict_types=1);

namespace PhpDb\Paginator\Adapter;

class ConfigProvider
{
    /**
     * Return default phpdb-paginator-adapter configuration.
     *
     * @return array[]
     */
    public function __invoke(): array
    {
        return [
            'paginators' => $this->getPaginatorConfig(),
        ];
    }

    /**
     * Return default service mappings for the paginator adapter plugin manager.
     *
     * @return array<string, array<string, class-string>>
     */
    public function getPaginatorConfig(): array
    {
        return [
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
        ];
    }
}
