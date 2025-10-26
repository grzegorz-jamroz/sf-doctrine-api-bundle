<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Ifrost\DoctrineApiBundle\Query\DbalCondition;

/**
 * @extends ArrayCollection<int, DbalCondition>
 */
class ConditionCollection extends ArrayCollection
{
    public function __construct(array $elements = [])
    {
        parent::__construct(self::getConditions($elements));
    }

    /**
     * @param array<int|string, mixed> $conditions
     *
     * @return array<int, DbalCondition>
     */
    public static function getConditions(array $conditions): array
    {
        $output = [];

        foreach ($conditions as $condition) {
            if ($condition instanceof DbalCondition) {
                $output[] = $condition;

                continue;
            }

            if (is_array($condition)) {
                /** @var array<string, mixed> $condition */
                $output[] = DbalCondition::createFromArray($condition);
            }
        }

        return $output;
    }
}
