<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Variant\Routing;

use Ifrost\DoctrineApiBundle\Routing\AnnotatedRouteControllerLoader;
use Ifrost\DoctrineApiBundle\Routing\DoctrineApiLoader;
use Symfony\Component\Config\FileLocator;

class DoctrineApiLoaderVariant extends DoctrineApiLoader
{
    public function __construct()
    {
        parent::__construct(new FileLocator(), new AnnotatedRouteControllerLoader());
    }

    public function getType(): string
    {
        return parent::getType();
    }
}
