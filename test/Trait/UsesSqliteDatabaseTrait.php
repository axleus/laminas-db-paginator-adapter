<?php

declare(strict_types=1);

namespace PhpDbTest\Paginator\Adapter\Trait;

use PhpDb\Adapter\Adapter;
use PhpDb\Sqlite\AdapterPlatform;
use PhpDb\Sqlite\Pdo\Connection;
use PhpDb\Sqlite\Pdo\Driver;

use function sprintf;

trait UsesSqliteDatabaseTrait
{
    private Adapter $adapter;

    /**
     * @param list<string> $titles
     */
    protected function seedPosts(array $titles): void
    {
        $this->adapter->query(
            'CREATE TABLE posts (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL)',
            Adapter::QUERY_MODE_EXECUTE,
        );

        foreach ($titles as $title) {
            $this->adapter->query(
                sprintf("INSERT INTO posts (title) VALUES ('%s')", $title),
                Adapter::QUERY_MODE_EXECUTE,
            );
        }
    }

    protected function setUpDatabase(): void
    {
        $connection    = new Connection(['dsn' => 'sqlite::memory:']);
        $driver        = new Driver($connection);
        $this->adapter = new Adapter($driver, new AdapterPlatform($driver));
    }

    /**
     * @param iterable<array-key, mixed> $items
     * @return list<string>
     */
    protected function titlesOf(iterable $items): array
    {
        $titles = [];
        foreach ($items as $item) {
            $titles[] = (string) $item['title'];
        }

        return $titles;
    }
}
