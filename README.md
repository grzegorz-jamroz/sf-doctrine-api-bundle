<h1 align="center">Ifrost Doctrine Api Bundle for Symfony</h1>

<p align="center">
    <strong>Bundle provides basic features for Symfony Doctrine Api</strong>
</p>

<p align="center">
    <img src="https://img.shields.io/badge/php->=8.1-blue?colorB=%238892BF" alt="Code Coverage">  
    <img src="https://img.shields.io/badge/coverage-100%25-brightgreen" alt="Code Coverage">   
    <img src="https://img.shields.io/badge/release-v0.0.1-blue" alt="Release Version">   
</p>

## Installation

```
composer require grzegorz-jamroz/sf-doctrine-api-bundle
```

#### Default config
You can add `config/packages/ifrost_doctrine_api.yaml` in your project to enable/disable some features if not necessary
```yaml
# config/packages/ifrost_doctrine_api.yaml
# default config
ifrost_doctrine_api:
    doctrine_dbal_types_uuid: true
    # instance of Psr\Cache\CacheItemPoolInterface
    dbal_cache_adapter: null
    dbal_cache_dir: null
  # ...
```

#### You can enable default cache adapter `Symfony\Component\Cache\Adapter\FilesystemAdapter` (optional)
```yaml 
# config/packages/ifrost_doctrine_api.yaml
ifrost_doctrine_api:
    doctrine_dbal_types_uuid: true
    dbal_cache_adapter: 'default'
    dbal_cache_dir: 'default'
# ...
```
