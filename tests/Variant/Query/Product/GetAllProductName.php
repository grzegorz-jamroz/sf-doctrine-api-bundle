<?php
declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Variant\Query\Product;

use Ifrost\DoctrineApiBundle\Query\DbalQuery;
use Ifrost\DoctrineApiBundle\Tests\Variant\Entity\Product;

class GetAllProductName extends DbalQuery
{
    protected function prepareQuery(): void
    {
        $this->select('name');
        $this->from(Product::getTableName());
    }
}
