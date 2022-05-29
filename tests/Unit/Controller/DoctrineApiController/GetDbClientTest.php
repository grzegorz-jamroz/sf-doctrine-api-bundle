<?php

declare(strict_types=1);

namespace Tests\Unit\Controller\DoctrineApiController;

use Ifrost\DoctrineApiBundle\Utility\DbClient;
use PHPUnit\Framework\TestCase;
use Tests\Variant\Controller\DoctrineApiControllerVariant;

class GetDbClientTest extends TestCase
{
    public function testShouldReturnInstanceOfDbClient()
    {
        // Given
        $controller = new DoctrineApiControllerVariant();

        // When & Then
        $this->assertInstanceOf(DbClient::class, $controller->getDbClient());
    }
}
