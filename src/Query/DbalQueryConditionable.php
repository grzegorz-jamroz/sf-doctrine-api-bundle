<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\Expression\CompositeExpression;

abstract class DbalQueryConditionable extends DbalQuery
{
    private DbalCriteria $criteria;

    public function __construct(
        Connection $connection,
        private string $tableName,
        ?DbalCriteria $criteria = null,
    ) {
        $this->criteria = $criteria ?? new DbalCriteria();
        parent::__construct($connection);
    }

    protected function prepareQuery(): void
    {
        $this->select('*');
        $this->from($this->tableName);
        $this->setMaxResults($this->criteria->getLimit());
        $this->setFirstResult($this->criteria->getOffset());
        $this->setCriteria();

        foreach ($this->criteria->getOrderings() as $field => $direction) {
            $this->addOrderBy($field, $direction);
        }
    }

    private function setCriteria(): void
    {
        if ($this->criteria->getConditions() === []) {
            return;
        }

        foreach ($this->criteria->getConditions() as $condition) {
            $operator = $condition->getOperator();

            if ($operator->isConjunction()) {
                $this->andWhere($this->conjunction($condition));
            }

            if ($operator->isComparison()) {
                $this->andWhere($this->comparison($condition));
            }

            if ($operator->isComparisonWithoutParam()) {
                $this->andWhere($this->comparisonWithoutParam($condition));
            }
        }
    }

    private function conjunction(DbalCondition $condition): CompositeExpression
    {
        $expressions = [];
        $op = $condition->getOperator()->value;

        foreach ($condition->getConditions() as $condition) {
            $operator = $condition->getOperator();

            if ($operator->isConjunction()) {
                $expressions[] = $this->conjunction($condition);
            }

            if ($operator->isComparison()) {
                $expressions[] = $this->comparison($condition);
            }

            if ($operator->isComparisonWithoutParam()) {
                $expressions[] = $this->comparisonWithoutParam($condition);
            }
        }

        /** @var 'and'|'or' $op */
        return $this->expr()->$op(...$expressions);
    }

    private function comparison(DbalCondition $condition): string
    {
        $op = $condition->getOperator()->value;
        /** @var 'eq'|'neq'|'lt'|'lte'|'gt'|'gte'|'like'|'notLike'|'in'|'notIn' $op */
        $expr = $this->expr()->$op($condition->getField(), '?');
        $this->addParameter($condition->getValue());

        return $expr;
    }

    private function comparisonWithoutParam(DbalCondition $condition): string
    {
        /** @var 'isNotNull'|'isNull' $op */
        $op = $condition->getOperator()->value;

        return $this->expr()->$op($condition->getField());
    }

    private function addParameter(mixed $value): void
    {
        $this->setParameters([
            ...$this->getParameters(),
            $value,
        ]);
    }
}
