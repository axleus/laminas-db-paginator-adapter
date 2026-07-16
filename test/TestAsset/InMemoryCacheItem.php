<?php

declare(strict_types=1);

namespace PhpDbTest\Paginator\Adapter\TestAsset;

use DateInterval;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;

final class InMemoryCacheItem implements CacheItemInterface
{
    public function __construct(
        private readonly string $key,
        private mixed $value = null,
        private bool $hit = false,
    ) {}

    public function expiresAfter(int|DateInterval|null $time): static
    {
        return $this;
    }

    public function expiresAt(?DateTimeInterface $expiration): static
    {
        return $this;
    }

    public function get(): mixed
    {
        return $this->value;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function isHit(): bool
    {
        return $this->hit;
    }

    public function set(mixed $value): static
    {
        $this->value = $value;
        $this->hit   = true;

        return $this;
    }
}
