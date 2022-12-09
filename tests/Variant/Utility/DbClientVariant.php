<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Variant\Utility;

use Doctrine\DBAL\Exception;
use Ifrost\DoctrineApiBundle\Utility\DbClient;

class DbClientVariant extends DbClient
{
    public function insert(string $table, array $data, array $types = []): void
    {
        throw new Exception('Unknown error occurred');
    }

    public function update($table, array $data, array $criteria, array $types = []): void
    {
        throw new Exception('Unknown error occurred');
    }
}
