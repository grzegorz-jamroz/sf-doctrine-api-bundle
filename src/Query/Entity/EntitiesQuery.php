<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Query\Entity;

use Doctrine\DBAL\Connection;
use Ifrost\DoctrineApiBundle\Query\DbalCriteria;
use Ifrost\DoctrineApiBundle\Query\DbalQueryConditionable;

class EntitiesQuery extends DbalQueryConditionable
{
    public function __construct(
        Connection $connection,
        private string $tableName,
        ?DbalCriteria $criteria = null
    ) {
        parent::__construct($connection, $criteria);
    }

    protected function prepareQuery(): void
    {
        parent::prepareQuery();
        $this->select('*');
        $this->from($this->tableName);
    }
}
