<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Query\Entity;

use Doctrine\DBAL\Connection;
use Ifrost\DoctrineApiBundle\Query\DbalQuery;

class EntityQuery extends DbalQuery
{
    public function __construct(
        Connection $connection,
        private string $tableName,
        private string $uuid,
    ) {
        parent::__construct($connection);
    }

    protected function prepareQuery(): void
    {
        $this->select('*');
        $this->from($this->tableName);
        $this->where('uuid = :uuid');
        $this->setParameter('uuid', $this->uuid);
    }
}
