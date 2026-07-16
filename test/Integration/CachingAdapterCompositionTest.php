<?php

declare(strict_types=1);

namespace PhpDbTest\Paginator\Adapter\Integration;

use Laminas\Paginator\Adapter\CachingAdapter;
use Laminas\Paginator\Paginator;
use PhpDb\Adapter\Adapter;
use PhpDb\Paginator\Adapter\Select;
use PhpDb\Sql\Select as SqlSelect;
use PhpDbTest\Paginator\Adapter\TestAsset\InMemoryCacheItemPool;
use PhpDbTest\Paginator\Adapter\Trait\UsesSqliteDatabaseTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the composition documented in docs/book/v1/db-select.md: a Select
 * adapter wrapped in laminas-paginator's PSR-6 CachingAdapter.
 */
#[Group('integration')]
final class CachingAdapterCompositionTest extends TestCase
{
    use UsesSqliteDatabaseTrait;

    private InMemoryCacheItemPool $cache;

    private CachingAdapter $cachingAdapter;

    #[Test]
    public function cachedPageIsServedAfterUnderlyingDataChanges(): void
    {
        $firstFetch = $this->titlesOf($this->cachingAdapter->getItems(0, 2));

        $this->adapter->query(
            "UPDATE posts SET title = 'changed' WHERE id = 1",
            Adapter::QUERY_MODE_EXECUTE,
        );

        static::assertSame($firstFetch, $this->titlesOf($this->cachingAdapter->getItems(0, 2)));
    }

    #[Test]
    public function distinctPagesAreCachedUnderSeparateKeys(): void
    {
        $this->cachingAdapter->getItems(0, 2);
        $this->cachingAdapter->getItems(2, 2);

        static::assertTrue($this->cache->hasItem('posts-0-2'));
        static::assertTrue($this->cache->hasItem('posts-2-2'));
    }

    #[Test]
    public function paginatorReturnsItemsThroughCachingAdapter(): void
    {
        $paginator = new Paginator($this->cachingAdapter);
        $paginator->setItemCountPerPage(2);
        $paginator->setCurrentPageNumber(2);

        static::assertSame(['post-3', 'post-4'], $this->titlesOf($paginator->getCurrentItems()));
    }

    public function setUp(): void
    {
        $this->setUpDatabase();
        $this->seedPosts(['post-1', 'post-2', 'post-3', 'post-4', 'post-5']);

        $select = new SqlSelect('posts');
        $select->order('id ASC');

        $this->cache          = new InMemoryCacheItemPool();
        $this->cachingAdapter = new CachingAdapter(
            new Select($select, $this->adapter),
            'posts',
            $this->cache,
            null,
        );
    }
}
