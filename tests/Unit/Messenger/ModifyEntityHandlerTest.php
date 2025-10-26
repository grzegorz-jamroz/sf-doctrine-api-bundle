<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Messenger;

use Ifrost\DoctrineApiBundle\Entity\EntityInterface;
use Ifrost\DoctrineApiBundle\Messenger\Command\ModifyEntity;
use Ifrost\DoctrineApiBundle\Messenger\Handler\ModifyEntityHandler;
use Ifrost\DoctrineApiBundle\Utility\DbClientInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ModifyEntityHandlerTest extends TestCase
{
    public function testShouldThrowInvalidArgumentExceptionWhenGivenClassNameIsNotEntityInterface(): void
    {
        // Expect
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('$entityClassName has to be instance of %s', EntityInterface::class));

        // Given
        $command = new ModifyEntity(Uuid::v7()->toString(), \stdClass::class, []);
        $handler = new ModifyEntityHandler(
            $this->createMock(DbClientInterface::class),
            $this->createMock(EventDispatcherInterface::class)
        );

        // When & Then
        $handler($command);
    }
}
