<?php

namespace Ifrost\DoctrineApiBundle\Routing;

use Ifrost\ApiFoundation\Attribute\Api as ApiAttribute;
use Ifrost\ApiFoundation\Routing\AbstractAnnotatedRouteControllerLoader;

class AnnotatedRouteControllerLoader extends AbstractAnnotatedRouteControllerLoader
{
    protected function getAttributeClassName(): string
    {
        return ApiAttribute::class;
    }
}
