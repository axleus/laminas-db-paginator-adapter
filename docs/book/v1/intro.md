# Introduction

This library provides two adapters for [laminas/laminas-paginator](https://docs.laminas.dev/laminas-paginator):

- `PhpDb\Paginator\Adapter\Select`
- `PhpDb\Paginator\Adapter\TableGateway`

These provide pagination support for [php-db/phpdb](https://github.com/php-db/phpdb) SQL select and TableGateway features.

- [Select documentation](db-select.md)
- [TableGateway documentation](db-table-gateway.md)

Each is registered with the `Laminas\Paginator\AdapterPluginManager` via the `Module` class in laminas-mvc applications, or via the `ConfigProvider` class in applications using config providers, such as Mezzio applications.
