<h1 align="center">Ifrost Doctrine Api Bundle for Symfony</h1>

<p align="center">
    <strong>Bundle provides basic features for Symfony Doctrine Api</strong>
</p>

<p align="center">
    <img src="https://img.shields.io/badge/php->=8.4-blue?colorB=%238892BF" alt="Code Coverage">  
    <img src="https://img.shields.io/badge/coverage-100%25 files|100%25 lines-brightgreen" alt="Code Coverage">   
    <img src="https://img.shields.io/badge/release-v7.0.0-blue" alt="Release Version">   
</p>

# Table of Contents
- [Installation](#installation)
- [Customizations](#customizations)
- [Configuration](#configuration)
- [Development with Docker](#development-with-docker)

# Installation

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

3. Create Entity which implements [EntityInterface](src/Entity/EntityInterface.php)

**Exmple:**
```php
<?php
// src/Entity/Product.php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Ifrost\DoctrineApiBundle\Entity\EntityInterface;
use PlainDataTransformer\Transform;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product implements EntityInterface
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid $uuid;

    #[ORM\Column(length: 255)]
    private string $name;

    public function __construct(
        Uuid $uuid,
        string $name,
    ) {
        $this->uuid = $uuid;
        $this->name = $name;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public static function getTableName(): string
    {
        return 'product';
    }

    /**
     * @return array<int, string>
     */
    public static function getFields(): array
    {
        return array_keys(self::createFromArray([])->getWritableFormat());
    }

    public function getWritableFormat(): array
    {
        return [
            'uuid' => $this->uuid->toBinary(),
            'name' => $this->name,
        ];
    }

    public function jsonSerialize(): array
    {
        return [
            'uuid' => (string) $this->uuid,
            'name' => $this->name,
        ];
    }

    public static function createFromArray(array $data): static|self
    {
        return new self(
            $data['uuid'] ?? Uuid::v7(),
            Transform::toString($data['name'] ?? ''),
        );
    }

    public static function createFromRequest(array $data): static|self
    {
        return new self(
            $data['uuid'] === null ? Uuid::v7() : Uuid::fromString($data['uuid']),
            Transform::toString($data['name'] ?? ''),
        );
    }
}
```

4. Create your controller:

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

5. Now you can debug your routes. Run command:

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

# Customizations

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
    dbal_cache_adapter:
      enabled: false
    db_client:
      enabled: true
  # ...
```

#### You can enable default cache adapter `Symfony\Component\Cache\Adapter\FilesystemAdapter` (optional)
```yaml 
# config/packages/ifrost_doctrine_api.yaml
ifrost_doctrine_api:
    dbal_cache_adapter:
      enabled: true
    db_client:
      enabled: true
# ...
```

---

# Development with Docker

### Build and run the containers:
```shell
docker compose up -d
```

### Copy vendor folder from container to host

```shell
docker compose cp app:/app/vendor ./vendor
```

### Run static analysis

```shell
docker compose exec app bin/fix
```

### Run tests

```shell
docker compose exec app bin/test
```

Run single test file:

```shell
docker compose exec app vendor/bin/phpunit --filter <testMethodName> <path/to/TestFile.php>
docker compose exec app vendor/bin/phpunit --filter testShouldReturnExpectedFloat tests/Unit/TransformNumeric/ToFloatTest.php
```

### Run coverage report

1. [Enable xdebug](#enable-xdebug)
2. Run:
```shell
docker compose exec app bin/coverage
```


### Enable xdebug

```shell
docker compose exec app xdebug on
```

### Disable xdebug

```shell
docker compose exec app xdebug off
```
