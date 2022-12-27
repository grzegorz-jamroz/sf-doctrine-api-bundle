<h1 align="center">Ifrost Doctrine Api Bundle for Symfony</h1>

<p align="center">
    <strong>Bundle provides basic features for Symfony Doctrine Api</strong>
</p>

<p align="center">
    <img src="https://img.shields.io/badge/php->=8.1-blue?colorB=%238892BF" alt="Code Coverage">  
    <img src="https://img.shields.io/badge/coverage-100%25-brightgreen" alt="Code Coverage">   
    <img src="https://img.shields.io/badge/release-v6.1.5-blue" alt="Release Version">   
</p>

## Installation

```
composer require grzegorz-jamroz/sf-doctrine-api-bundle
```

1. Update routing configuration in your project:

```yaml
# config/routes.yaml
controllers:
    resource: ../src/Controller/
    type: attribute

# ...

# add those lines:
ifrost_doctrine_api_controllers:
    resource: ../src/Controller/
    type: doctrine_api_attribute
    
# ...
```

2. Create your controller:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use Ifrost\ApiFoundation\Attribute\Api;
use Ifrost\DoctrineApiBundle\Controller\DoctrineApiController;

#[Api(entity: Product::class, path: 'products')]
class ProductController extends DoctrineApiController
{
}
```

3. Now you can debug your routes. Run command:

```
php bin/console debug:router
```

you should get output:

```
 ------------------- -------- -------- ------ -------------------------- 
  Name                Method   Scheme   Host   Path                      
 ------------------- -------- -------- ------ -------------------------- 
  _preview_error      ANY      ANY      ANY    /_error/{code}.{_format}  
  products_find       GET      ANY      ANY    /products                 
  products_find_one   GET      ANY      ANY    /products/{uuid}          
  products_create     POST     ANY      ANY    /products                 
  products_update     PUT      ANY      ANY    /products/{uuid}          
  products_modify     PATCH    ANY      ANY    /products/{uuid}          
  products_delete     DELETE   ANY      ANY    /products/{uuid}          
 ------------------- -------- -------- ------ -------------------------- 
```

## More custom usage

If you decided that you want to change routing configuration for some specific route just add `Route` attribute with new parameters. For example:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use Ifrost\ApiFoundation\Attribute\Api;
use Ifrost\DoctrineApiBundle\Controller\DoctrineApiController;
use Symfony\Component\Routing\Annotation\Route;

#[Api(
    entity: Product::class,
    path: 'products'
)]
class ProductController extends DoctrineApiController
{
    #[Route('/create_products', name: 'products_create', methods: ['POST'])]
    public function create(): Response
    {
        return $this->getApi()->create();
    }
}
```

now output from `php bin/console debug:router` will be:

```
 ------------------- -------- -------- ------ -------------------------- 
  Name                Method   Scheme   Host   Path                      
 ------------------- -------- -------- ------ -------------------------- 
  _preview_error      ANY      ANY      ANY    /_error/{code}.{_format}  
  products_create     POST     ANY      ANY    /create_products          
  products_find       GET      ANY      ANY    /products                 
  products_find_one   GET      ANY      ANY    /products/{uuid}          
  products_update     PUT      ANY      ANY    /products/{uuid}          
  products_modify     PATCH    ANY      ANY    /products/{uuid}          
  products_delete     DELETE   ANY      ANY    /products/{uuid}          
 ------------------- -------- -------- ------ -------------------------- 
```

It is possible do disable some actions at all. In this case you can use `excludedActions` metadata.

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use Ifrost\ApiFoundation\Attribute\Api;
use Ifrost\DoctrineApiBundle\Controller\DoctrineApiController;

#[Api(
    entity: Product::class,
    path: 'products',
    excludedActions: [
        Action::CREATE,
        Action::UPDATE,
        Action::MODIFY,
        'delete',
        'not_valid_actions_will_be_omitted'
    ])]
)]
class ProductController extends DoctrineApiController
{
}
```

now output from `php bin/console debug:router` will be:

```
 ------------------- -------- -------- ------ --------------------------
  Name                Method   Scheme   Host   Path
 ------------------- -------- -------- ------ --------------------------
  _preview_error      ANY      ANY      ANY    /_error/{code}.{_format}
  products_find       GET      ANY      ANY    /products
  products_find_one   GET      ANY      ANY    /products/{uuid}
 ------------------- -------- -------- ------ --------------------------
```

## Configuration

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
