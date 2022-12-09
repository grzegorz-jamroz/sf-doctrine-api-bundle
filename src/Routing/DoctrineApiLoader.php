<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Routing;

use Ifrost\ApiFoundation\Routing\AbstractApiLoader;

class DoctrineApiLoader extends AbstractApiLoader
{
    protected function getType(): string
    {
        return 'doctrine_api_attribute';
    }
}
