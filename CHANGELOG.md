# Yii3 Doctrine Change Log

## 1.1.0

#### Feat: 
1. Move fixture on package `fcnybok/yii-doctrine-fixture`.
2. Add command for migration `doctrine:migrations:version`.
3. Update dependency.
4. Small fix code. 

#### Update: 
File [di-console.php](config/di.php), [params](config/params.php)

Add [VersionCommand.php](src/Migrations/Command/VersionCommand.php)

## 1.0.11

#### Feat: 
Add reset entity manager for RoadRunner application.

#### Update:
File [di-console.php](config/di.php)

## 1.0.10

#### Fix:
Update dependency, DI console

#### Update:
File: [di-console.php](config/di-console.php)

## 1.0.9
#### Feat: 
Add configure params for entity manager `entity_listener_resolver` and `typed_field_mapper`, fix code.

#### Update:
Class [ConfigurationFactory](src/Orm/Factory/ConfigurationFactory.php) add methods `configureEntityListenerResolver` and `configureTypedFieldMapper`

File [example.php](config/example.php)

## 1.0.8

#### Update:

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

## 1.0.7

- Fix: Update command name

## 1.0.6

- Fix: Update dependency, fix di config.

## 1.0.5

- Fix: Update dependency, refactoring create configuration DBAL and ORM

## 1.0.4

- Fix example config

## 1.0.3

- Fix code
- Update readme

## 1.0.2

- Fix code style
- Update readme

## 1.0.1

- Configuration dbal middlewares
- Update readme

## 1.0.0 under development

- Initial release.
