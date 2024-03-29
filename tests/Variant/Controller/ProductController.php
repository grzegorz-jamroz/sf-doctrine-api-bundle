<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Variant\Controller;

use Ifrost\ApiFoundation\Attribute\Api;
use Ifrost\DoctrineApiBundle\Tests\Variant\Entity\Product;

#[Api(entity: Product::class, path: 'products')]
class ProductController extends DoctrineApiControllerVariant
{
}
