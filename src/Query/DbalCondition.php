<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Query;

use Ifrost\DoctrineApiBundle\Collection\ConditionCollection;
use Ifrost\Foundations\ArrayConstructable;
use InvalidArgumentException;
use PlainDataTransformer\Transform;

class DbalCondition implements ArrayConstructable
{
    /**
     * @var DbalCondition[]
     */
    private array $conditions;

    /**
     * @param array<int|string, mixed|DbalCondition> $conditions required only for DbalOperator::AND and DbalOperator::OR
     */
    public function __construct(
        private DbalOperator $operator,
        private string $field,
        private string $value,
        array $conditions = [],
    ) {
        $this->conditions = ConditionCollection::getConditions($conditions);
    }

    public function getOperator(): DbalOperator
    {
        return $this->operator;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return DbalCondition[] $conditions
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function createFromArray(array $data): self
    {
        $operator = DbalOperator::tryFrom(Transform::toString($data['operator'] ?? '')) ?? '';

        if ($operator === '') {
            throw new InvalidArgumentException(sprintf('Provided operator "%s" is invalid.', Transform::toString($data['operator'] ?? '')));
        }

        return new self(
            $operator,
            Transform::toString($data['field'] ?? ''),
            Transform::toString($data['value'] ?? ''),
            Transform::toArray($data['conditions'] ?? []),
        );
    }
}
