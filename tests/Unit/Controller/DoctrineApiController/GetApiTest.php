<?php

declare(strict_types=1);

namespace Tests\Unit\Controller\DoctrineApiController;

use Ifrost\DoctrineApiBundle\Utility\DefaultApi;
use PHPUnit\Framework\TestCase;
use Tests\Variant\Controller\DoctrineApiControllerVariant;
use Tests\Variant\Entity\Product;

class GetApiTest extends TestCase
{
    public function testShouldReturnInstanceOfDbClient()
    {
        // Given
        $controller = new DoctrineApiControllerVariant();

        // When & Then
        $this->assertInstanceOf(DefaultApi::class, $controller->getApi(Product::class));
    }
}
