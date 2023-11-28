<?php
declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Variant\Query\Product;

use Doctrine\DBAL\Connection;
use Ifrost\DoctrineApiBundle\Query\DbalQuery;
use Ifrost\DoctrineApiBundle\Tests\Variant\Entity\Product;

class GetProductRate extends DbalQuery
{
    public function __construct(
        Connection $connection,
        private string $uuid,
    ) {
        parent::__construct($connection);
    }

    protected function prepareQuery(): void
    {
        $this->select('rate');
        $this->from(Product::getTableName());
        $this->where('uuid = :uuid');
        $this->setParameter('uuid', $this->uuid);
    }
}
