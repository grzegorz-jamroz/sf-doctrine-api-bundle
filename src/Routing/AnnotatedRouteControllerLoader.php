<?php

namespace Ifrost\DoctrineApiBundle\Routing;

use Ifrost\ApiFoundation\Attribute\ApiController;
use Ifrost\ApiFoundation\Routing\AbstractAnnotatedRouteControllerLoader;

class AnnotatedRouteControllerLoader extends AbstractAnnotatedRouteControllerLoader
{
    protected function getAttributeClassName(): string
    {
        return ApiController::class;
    }
}
