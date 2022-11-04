# !!! UNDER DEVELOPMENT !!!


## Symfony Cycle ORM support

Cycle is a PHP DataMapper ORM and Data Modelling engine designed to safely work
in classic and daemonized PHP applications such as [RoadRunner](https://github.com/roadrunner-server/roadrunner).
This package provides a convenient way to integrate Cycle ORM v2 with Symfony.

Read more in the [official documentation](https://cycle-orm.dev/docs/readme/2.x)


## Requirements

Make sure that your server is configured with following PHP version and extensions:

- PHP 8.1+
- PDO Extension with desired database drivers


## Installation

To install the package:

```bash
composer require mamchyts/cycle-symfony-bundle
```


## Full diff of symfony-demo migration from Doctrine to Cycle ORM

Diff more info [link](https://github.com/mamchyts/symfony-demo/pull/1/files)


## Blocker/issues

List below contains all @todo tags in project:

* Issue (first) with EntityProxyInterface wrapper: https://github.com/mamchyts/symfony-demo/blob/cycle/src/Controller/BlogController.php#L106
* Issue with DatabaseProviderInterface: https://github.com/mamchyts/cycle-symfony-bundle/blob/master/src/Command/DatabaseDropTablesCommand.php#L30
* Issue with ClassLocator: https://github.com/mamchyts/cycle-symfony-bundle/blob/master/src/Command/MigrationDiffCommand.php#L38
* Issue with detection PK value: https://github.com/mamchyts/cycle-symfony-bundle/blob/master/src/DependencyInjection/Security/EntityUserProvider.php#L101
* Issue (second) with EntityProxyInterface wrapper: https://github.com/mamchyts/cycle-symfony-bundle/blob/master/src/DependencyInjection/Security/EntityUserProvider.php#L106
* Issue with migration files structure generator: https://github.com/mamchyts/cycle-symfony-bundle/blob/master/src/Migration/FileRepository.php#L12
