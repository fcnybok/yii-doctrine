# Yii3 Doctrine Change Log

## 1.0.0 under development

- Initial release.

## 1.0.1

- Configuration dbal middlewares
- Update readme

## 1.0.2

- Fix code style
- Update readme

## 1.0.3

- Fix code
- Update readme

## 1.0.4

- Fix example config

## 1.0.5

- Fix: Update dependency, refactoring create configuration DBAL and ORM

## 1.0.6

- Fix: Update dependency, fix di config.

## 1.0.7

- Fix: Update command name

## 1.0.8

Update:

Class [DoctrineManagerFactory](src/Factory/DoctrineManagerFactory.php)
- Add method `create`
- Remove method `__invoke`

Class [MigrationConfigurationFactory](src/Migrations/Factory/MigrationConfigurationFactory.php)
- Add method `create`
- Remove method `__invoke`

Class [FixtureLoaderManager](src/Fixture/FixtureLoaderManager.php)
- Add method `create`
- Remove method `__invoke`

File [di.php](config/di.php)
File [di-console.php](config/di-console.php)