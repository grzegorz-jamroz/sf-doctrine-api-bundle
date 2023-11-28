<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Variant\Query\Product;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Ifrost\DoctrineApiBundle\Query\DbalQuery;
use Ifrost\DoctrineApiBundle\Tests\Variant\Entity\Product;

class GetAllProducts extends DbalQuery
{
    protected function prepareQuery(): void
    {
        $this->select('*');
        $this->from(Product::getTableName());
    }

    public function getQueryCacheProfile(): ?QueryCacheProfile
    {
        return new QueryCacheProfile(3600, 'GetAllProducts', $this->getResultCache());
    }
}
