<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Query\DbalQuery;

use Ifrost\DoctrineApiBundle\Query\DbalQuery;
use PHPUnit\Framework\TestCase;
use Ifrost\DoctrineApiBundle\Tests\Variant\Controller\DoctrineApiControllerVariant;
use Ifrost\DoctrineApiBundle\Tests\Variant\Sample;

class FetchAllTest extends TestCase
{
    public function testShouldThrowRuntimeExceptionWhenTryingToExecuteQueryWhichDoesNotExtendDbalQuery()
    {
        // Expect & Given
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Query is invalid. Query should be instance of %s (%s given).', DbalQuery::class, gettype(new Sample())));
        $controller = new DoctrineApiControllerVariant();
        $controller->getContainer()->set('ifrost_doctrine_api.dbal_cache_adapter', null);

        // When & Then
        $controller->getDbClient()->fetchAll(Sample::class);
    }
}
