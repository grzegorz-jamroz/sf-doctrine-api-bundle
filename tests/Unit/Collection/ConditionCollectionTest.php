<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Collection;

use Ifrost\DoctrineApiBundle\Collection\ConditionCollection;
use Ifrost\DoctrineApiBundle\Query\DbalCondition;
use Ifrost\DoctrineApiBundle\Query\DbalOperator;
use PHPUnit\Framework\TestCase;

class ConditionCollectionTest extends TestCase
{
    public function testShouldReturnConditionCollectionWithThreeElements()
    {
        // Given
        $conditions = [
            new DbalCondition(DbalOperator::GT, 'rate', '3'),
            new DbalCondition(DbalOperator::IS_NOT_NULL, 'rate', ''),
            [
                'operator' => DbalOperator::LIKE->value,
                'field' => 'name',
                'value' => '%guitar%',
            ],
        ];

        // When
        $collection = new ConditionCollection($conditions);

        // Then
        $this->assertCount(3, $collection);
    }
}
