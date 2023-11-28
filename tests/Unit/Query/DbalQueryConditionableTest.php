<?php
declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Ifrost\DoctrineApiBundle\Query\DbalCondition;
use Ifrost\DoctrineApiBundle\Query\DbalCriteria;
use Ifrost\DoctrineApiBundle\Query\DbalOperator;
use Ifrost\DoctrineApiBundle\Query\Entity\EntitiesQuery;
use Ifrost\Filesystem\JsonFile;
use PHPUnit\Framework\TestCase;
use PlainDataTransformer\Transform;
use Ifrost\DoctrineApiBundle\Tests\Variant\Entity\Product;

class DbalQueryConditionableTest extends TestCase
{
    private Connection $dbal;

    protected function setUp(): void
    {
        $this->dbal = DriverManager::getConnection([
            'url' => Transform::toString($_ENV['DATABASE_URL'] ?? ''),
        ]);
    }

    public function testShouldReturnProperQueryForDbalCondition()
    {
        // Given
        $condition = new DbalCondition(DbalOperator::OR, '', '', [
            new DbalCondition(DbalOperator::GT, 'rate', '3'),
            new DbalCondition(DbalOperator::IS_NOT_NULL, 'rate', ''),
            new DbalCondition(DbalOperator::LIKE, 'name', '%guitar%'),
        ]);

        // When
        $criteria = new DbalCriteria([$condition]);

        // Then
        $this->assertEquals('SELECT * FROM product WHERE (rate > ?) OR (rate IS NOT NULL) OR (name LIKE ?) LIMIT 10', (new EntitiesQuery($this->dbal, Product::getTableName(), $criteria))->getSQL());
    }

    public function testShouldReturnProperQueryForEachCompareOperation()
    {
        // Given
        $variants = [
            [
                'operator' => DbalOperator::EQ,
                'expectedSql' => 'SELECT * FROM product WHERE code = ? LIMIT 10',
            ],
            [
                'operator' => DbalOperator::NEQ,
                'expectedSql' => 'SELECT * FROM product WHERE code <> ? LIMIT 10',
            ],
            [
                'operator' => DbalOperator::LT,
                'expectedSql' => 'SELECT * FROM product WHERE code < ? LIMIT 10',
            ],
            [
                'operator' => DbalOperator::LTE,
                'expectedSql' => 'SELECT * FROM product WHERE code <= ? LIMIT 10',
            ],
            [
                'operator' => DbalOperator::GT,
                'expectedSql' => 'SELECT * FROM product WHERE code > ? LIMIT 10',
            ],
            [
                'operator' => DbalOperator::GTE,
                'expectedSql' => 'SELECT * FROM product WHERE code >= ? LIMIT 10',
            ],
            [
                'operator' => DbalOperator::LIKE,
                'expectedSql' => 'SELECT * FROM product WHERE code LIKE ? LIMIT 10',
            ],
            [
                'operator' => DbalOperator::NOT_LIKE,
                'expectedSql' => 'SELECT * FROM product WHERE code NOT LIKE ? LIMIT 10',
            ],
            [
                'operator' => DbalOperator::IN,
                'expectedSql' => 'SELECT * FROM product WHERE code IN (?) LIMIT 10',
            ],
            [
                'operator' => DbalOperator::NOT_IN,
                'expectedSql' => 'SELECT * FROM product WHERE code NOT IN (?) LIMIT 10',
            ],
            [
                'operator' => DbalOperator::IS_NULL,
                'expectedSql' => 'SELECT * FROM product WHERE code IS NULL LIMIT 10',
            ],
            [
                'operator' => DbalOperator::IS_NOT_NULL,
                'expectedSql' => 'SELECT * FROM product WHERE code IS NOT NULL LIMIT 10',
            ],
        ];

        // When & Then
        foreach ($variants as $variant) {
            $criteria = new DbalCriteria([
                new DbalCondition($variant['operator'], 'code', 'STY639PW1'),
            ]);
            $this->assertEquals($variant['expectedSql'], (new EntitiesQuery($this->dbal, Product::getTableName(), $criteria))->getSQL());
        }
    }

    public function testShouldReturnProperQueryForComplexCondition(): void
    {
        // Given
        $variantOne = (new JsonFile(sprintf('%s/conditions/criteria-1.json', TESTS_DATA_DIRECTORY)))->read();
        $variantTwo = [
            [
                'operator' => DbalOperator::OR->value,
                'conditions' => $variantOne,
            ],
        ];
        $variantOneExpectedQuery = <<<SQL
SELECT * FROM product WHERE (name = ?) AND (((description LIKE ?) AND (rate > ?)) OR ((description LIKE ?) AND (rate < ?))) LIMIT 10
SQL;
        $variantTwoExpectedQuery = <<<SQL
SELECT * FROM product WHERE (name = ?) OR (((description LIKE ?) AND (rate > ?)) OR ((description LIKE ?) AND (rate < ?))) LIMIT 10
SQL;

        // When & Then
        $conditions = array_map(fn(array $conditionData) => DbalCondition::createFromArray($conditionData), $variantOne);
        $criteria = new DbalCriteria($conditions);
        $this->assertEquals($variantOneExpectedQuery, (new EntitiesQuery($this->dbal, Product::getTableName(), $criteria))->getSQL());

        $conditions = array_map(fn(array $conditionData) => DbalCondition::createFromArray($conditionData), $variantTwo);
        $criteria = new DbalCriteria($conditions);
        $this->assertEquals($variantTwoExpectedQuery, (new EntitiesQuery($this->dbal, Product::getTableName(), $criteria))->getSQL());
    }

    public function testShouldThrowInvalidArgumentExceptionWhenOperatorIsInvalid()
    {
        // Given
        $operators = [
            null,
            '>>'
        ];

        // Expect
        foreach ($operators as $operator) {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage(sprintf('Provided operator "%s" is invalid.', $operator));
            DbalCondition::createFromArray([
                'operator' => $operator,
                'field' => 'code',
                'value' => 'STY639PW1',
            ]);
        }
    }
}
