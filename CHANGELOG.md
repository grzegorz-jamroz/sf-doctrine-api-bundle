# Changelog
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

[v6.1.5]: https://github.com/grzegorz-jamroz/sf-storage-api-bundle/releases/tag/v6.1.5]
[v6.1.4]: https://github.com/grzegorz-jamroz/sf-storage-api-bundle/releases/tag/v6.1.4]
[v6.1.3]: https://github.com/grzegorz-jamroz/sf-storage-api-bundle/releases/tag/v6.1.3]
[v6.1.2]: https://github.com/grzegorz-jamroz/sf-storage-api-bundle/releases/tag/v6.1.2]
