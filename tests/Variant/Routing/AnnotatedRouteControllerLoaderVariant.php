<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Variant\Routing;

use Ifrost\DoctrineApiBundle\Routing\AnnotatedRouteControllerLoader;

class AnnotatedRouteControllerLoaderVariant extends AnnotatedRouteControllerLoader
{
    public function getAttributeClassName(): string
    {
        return parent::getAttributeClassName();
    }
}
