<?php

declare(strict_types=1);

namespace PhpDbTest\Paginator\Adapter\TestAsset;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

use function array_key_exists;

final class InMemoryCacheItemPool implements CacheItemPoolInterface
{
    /** @var array<string, mixed> */
    private array $values = [];

    public function clear(): bool
    {
        $this->values = [];

        return true;
    }

    public function commit(): bool
    {
        return true;
    }

    public function deleteItem(string $key): bool
    {
        unset($this->values[$key]);

        return true;
    }

    /**
     * @param string[] $keys
     */
    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }

        return true;
    }

    public function getItem(string $key): CacheItemInterface
    {
        if (array_key_exists($key, $this->values)) {
            return new InMemoryCacheItem($key, $this->values[$key], true);
        }

        return new InMemoryCacheItem($key);
    }

    /**
     * @param string[] $keys
     * @return iterable<string, CacheItemInterface>
     */
    public function getItems(array $keys = []): iterable
    {
        $items = [];
        foreach ($keys as $key) {
            $items[$key] = $this->getItem($key);
        }

        return $items;
    }

    public function hasItem(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }

    public function save(CacheItemInterface $item): bool
    {
        $this->values[$item->getKey()] = $item->get();

        return true;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        return $this->save($item);
    }
}
