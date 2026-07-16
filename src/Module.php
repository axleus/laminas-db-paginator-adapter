<?php

declare(strict_types=1);

namespace PhpDb\Paginator\Adapter;

final class Module
{
    /**
     * Return default phpdb-paginator-adapter configuration.
     *
     * @return array[]
     */
    public function getConfig(): array
    {
        $provider = new ConfigProvider();

        return [
            'paginators' => $provider->getPaginatorConfig(),
        ];
    }
}
