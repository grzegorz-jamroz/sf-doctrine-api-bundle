<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Ifrost\DoctrineApiBundle\Query\DbalCondition;

class ConditionCollection extends ArrayCollection
{
    public function __construct(array $elements = [])
    {
        parent::__construct(self::getConditions($elements));
    }

    /**
     * @param array<int|string, mixed> $conditions
     *
     * @return DbalCondition[]
     */
    public static function getConditions(array $conditions): array
    {
        return array_reduce(
            $conditions,
            function (array $acc, mixed $condition) {
                if ($condition instanceof DbalCondition) {
                    $acc[] = $condition;
                }

                if (is_array($condition)) {
                    $acc[] = DbalCondition::createFromArray($condition);
                }

                return $acc;
            },
            [],
        );
    }
}
