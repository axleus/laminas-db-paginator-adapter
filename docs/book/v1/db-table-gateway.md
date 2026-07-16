# The TableGateway adapter

The `TableGateway` adapter allows you to provide a `PhpDb\TableGateway\AbstractTableGateway` extension to both pull a dataset and provide a count of results.

By default, it assumes you want to fetch all items from the table.
However, the adapter also allows you to provide WHERE, ORDER BY, GROUP BY, and HAVING clauses to refine your selection; the WHERE and HAVING arguments accept `PhpDb\Sql\Where` and `PhpDb\Sql\Having` instances respectively, in addition to strings, arrays, and closures.

The items returned by the adapter will be based on the `PhpDb\ResultSet\ResultSetInterface` result set prototype you associate with the table gateway.

## Creating An Instance

The `TableGateway` constructor has the following signature:

```php
public function __construct(
    \PhpDb\TableGateway\AbstractTableGateway $tableGateway,
    null|string|array|\Closure|\PhpDb\Sql\Where $where = null,
    null|string|array $order = null,
    null|string|array $group = null,
    null|string|array|\Closure|\PhpDb\Sql\Having $having = null
)
```

The first argument is the `AbstractTableGateway` class extension representing the table you want to fetch results from.
The second argument represents the WHERE criteria for filtering results; see the [phpdb Where documentation](https://github.com/php-db/phpdb/) for details on what is accepted.
The third argument represents the order in which results should be sorted.
The fourth argument represents how results should be grouped.
The fifth argument represents a HAVING clause, which is generally used when grouping records.
See the [phpdb Sql documentation](https://github.com/php-db/phpdb/) for details on each.

### Using the AdapterPluginManager

By default, when pulling the `Laminas\Paginator\AdapterPluginManager` from the application DI container, it is aware of the `TableGateway` adapter.
You can retrieve an instance from the plugin manager via its `get()` method, passing any constructor arguments you want to provide via an array as the second argument:

```php
use Laminas\Paginator\AdapterPluginManager;
use PhpDb\Paginator\Adapter\TableGateway;

// $container is the PSR-11 container associated with the application.
$pluginManager = $container->get(AdapterPluginManager::class);

// $table is the phpdb TableGateway instance for retrieving items
$adapter = $pluginManager->get(TableGateway::class, [$table]);
```

All required arguments to the constructor must be passed in the array, and they will be passed in the same order to the constructor.

## Counting Total Items

The `TableGateway` adapter extends the [Select adapter](db-select.md); during instantiation, it retrieves both the base `PhpDb\Sql\Sql` instance and the `PhpDb\ResultSet\ResultSetInterface` prototype composed in the table gateway, creates a `PhpDb\Sql\Select` instance, and passes all three to the parent constructor.
The `Select` instance is thus used as the basis for the count operation as well.

Because there is no way to provide an alternate `Select` for counting, you have two options: extend the `TableGateway` adapter and override the `count()` method, or create your own `Select` instances for fetching items and the count and pass them to the `Select` adapter constructor instead.

### Overriding the Count Method

The following example demonstrates extending the `TableGateway` adapter to override the `count()` method.

```php
namespace App;

use PhpDb\Sql\Select;
use PhpDb\Paginator\Adapter\TableGateway;

class MyTableGateway extends TableGateway
{
    public function count(): int
    {
        if ($this->rowCount) {
            return $this->rowCount;
        }

        $select = new Select();
        $select
            ->from('item_counts')
            ->columns(['c' => 'post_count']);

        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();
        $row       = $result->current();

        $this->rowCount = (int) $row['c'];

        return $this->rowCount;
    }
}

// $tableGateway is the phpdb TableGateway for retrieving items
$adapter = new MyTableGateway($tableGateway);
```

### Creating Select Statements to Pass to a Select Adapter

The following example demonstrates pulling the `Sql` instance associated with the `TableGateway` instance, using it to create `Select` instances for pulling items and generating a count, and then using all of them together to create a `Select` adapter instance.

```php
use PhpDb\Paginator\Adapter\Select;

// $tableGateway is the phpdb TableGateway we want to use
$sql    = $tableGateway->getSql();
$select = $sql->select();
// Manipulate the $select to retrieve the result set you want.
// ...
$count = $sql->select();
// Manipulate the $count to generate the item count you want,
// aliasing the count column to Select::ROW_COUNT_COLUMN_NAME.
// ...

// Create the adapter
$adapter = new Select(
    $select,
    $sql,
    $tableGateway->getResultSetPrototype(),
    $count
);
```
