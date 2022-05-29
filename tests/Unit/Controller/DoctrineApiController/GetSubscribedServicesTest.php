<?php
declare(strict_types=1);

namespace Tests\Unit\Controller\DoctrineApiController;

use Doctrine\Persistence\ManagerRegistry;
use Ifrost\DoctrineApiBundle\Controller\DoctrineApiController as Controller;
use PHPUnit\Framework\TestCase;

class GetSubscribedServicesTest extends TestCase
{
    public function testShouldReturnArrayWithDesiredSubscribedServices(): void
    {
        // Given
        $services = Controller::getSubscribedServices();

        // When & Then
        $this->assertEquals($services['doctrine'], sprintf('?%s', ManagerRegistry::class));
    }
}
