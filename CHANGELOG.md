# Changelog
## Unreleased
### Add
- add [WithDbalWritableFormat](src/Entity/WithDbalWritableFormat.php) interface
- add [DbClientInterface](src/Utility/DbClientInterface.php) interface

### Change
- upgrade dependencies
- fix deprecations in DbalQuery
- extend [EntityInterface](src/Entity/EntityInterface.php) with [WithDbalWritableFormat](src/Entity/WithDbalWritableFormat.php)
- extend [DbClient](src/Utility/DbClient.php) with [DbClientInterface](src/Utility/DbClientInterface.php)

## [v6.1.6] - 2022.02.11
### Add
- [DoctrineApi](src/Utility/DoctrineApi.php)
  - [AfterFindEvent](src/Event/AfterFindEvent.php)
### Change
- extends [NotUniqueException](src/Exception/NotUniqueException.php) with `Ifrost\ApiFoundation\Exception\NotUniqueException`
- [DoctrineApi](src/Utility/DoctrineApi.php)
  - method `find` by default return results processed by `jsonSerialize` 
  - add possibility to return raw data in `find` method using option `raw_data`

## [v6.1.5] - 2022.12.27
### Add
- add before events 
  - [BeforeCreateEvent](src/Event/BeforeCreateEvent.php)
  - [BeforeUpdateEvent](src/Event/BeforeUpdateEvent.php)
  - [BeforeModifyEvent](src/Event/BeforeModifyEvent.php)
  - [BeforeDeleteEvent](src/Event/BeforeDeleteEvent.php)

## [v6.1.4] - 2022.12.14
### Change
- upgrade [sf-api-bundle](https://github.com/grzegorz-jamroz/sf-api-bundle) version to [v6.1.1](https://github.com/grzegorz-jamroz/sf-api-bundle/releases/tag/v6.1.1)

## [v6.1.3] - 2022.12.13
### Change
- [EntityInterface](src/Entity/EntityInterface.php)
  - add method getWritableFormat responsible for providing data which can be stored in database

## [v6.1.2] - 2022.12.09
### Change
- Transfer some responsibilities to [sf-api-foundation](https://github.com/grzegorz-jamroz/sf-api-foundation) package
### Add
- add Routing
- add EntityInterface for bundle
- extend DoctrineApiController with ApiControllerTrait
- add support for metadata `path` and `excludedActions` in Api attribute

[v6.1.6]: https://github.com/grzegorz-jamroz/sf-doctrine-api-bundle/releases/tag/v6.1.6]
[v6.1.5]: https://github.com/grzegorz-jamroz/sf-doctrine-api-bundle/releases/tag/v6.1.5]
[v6.1.4]: https://github.com/grzegorz-jamroz/sf-doctrine-api-bundle/releases/tag/v6.1.4]
[v6.1.3]: https://github.com/grzegorz-jamroz/sf-doctrine-api-bundle/releases/tag/v6.1.3]
[v6.1.2]: https://github.com/grzegorz-jamroz/sf-doctrine-api-bundle/releases/tag/v6.1.2]
