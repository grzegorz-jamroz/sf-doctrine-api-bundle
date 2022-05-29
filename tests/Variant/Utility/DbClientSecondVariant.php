<?php

declare(strict_types=1);

namespace Tests\Variant\Utility;

use Doctrine\DBAL\Exception;
use Ifrost\DoctrineApiBundle\Query\DbalQuery;
use Ifrost\DoctrineApiBundle\Utility\DbClient;

class DbClientSecondVariant extends DbClient
{
    public function fetchOne(string|DbalQuery $query, mixed ...$params): array
    {
        throw new Exception('Unknown error occurred in fetchOne');
    }
}
