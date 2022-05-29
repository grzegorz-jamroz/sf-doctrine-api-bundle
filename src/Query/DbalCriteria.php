<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Query;

use Ifrost\DoctrineApiBundle\Collection\ConditionCollection;
use Ifrost\Foundations\ArrayConstructable;
use PlainDataTransformer\Transform;

class DbalCriteria implements ArrayConstructable
{
    public const ASC = 'ASC';

    public const DESC = 'DESC';

    /**
     * @var DbalCondition[]
     */
    private array $conditions;

    /**
     * @param array<int|string, mixed|DbalCondition> $conditions
     * @param array<string, string>                  $orderings
     */
    public function __construct(
        array $conditions = [],
        private array $orderings = [],
        private int $offset = 0,
        private int $limit = 10
    ) {
        $this->conditions = ConditionCollection::getConditions($conditions);
    }

    /**
     * @return DbalCondition[]
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @return array<string, string>
     */
    public function getOrderings(): array
    {
        return $this->orderings;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public static function createFromArray(array $data): static|self
    {
        /** @var array<string, string> $orderBy */
        $orderBy = Transform::toArray($data['orderBy'] ?? []);

        return new self(
            Transform::toArray($data['conditions'] ?? []),
            $orderBy,
            Transform::toInt($data['offset'] ?? 0),
            Transform::toInt($data['limit'] ?? 10),
        );
    }
}
