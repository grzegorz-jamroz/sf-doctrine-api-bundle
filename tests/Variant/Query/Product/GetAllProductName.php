<?php
declare(strict_types=1);

namespace Tests\Variant\Query\Product;

use Ifrost\DoctrineApiBundle\Query\DbalQuery;
use Tests\Variant\Entity\Product;

class GetAllProductName extends DbalQuery
{
    protected function prepareQuery(): void
    {
        $this->select('name');
        $this->from(Product::TABLE);
    }
}
