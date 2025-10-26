<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Messenger;

use Ifrost\DoctrineApiBundle\Entity\EntityInterface;
use Ifrost\DoctrineApiBundle\Messenger\Command\DeleteEntity;
use Ifrost\DoctrineApiBundle\Messenger\Handler\DeleteEntityHandler;
use Ifrost\DoctrineApiBundle\Utility\DbClientInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DeleteEntityHandlerTest extends TestCase
{
    public function testShouldThrowInvalidArgumentExceptionWhenGivenClassNameIsNotEntityInterface(): void
    {
        // Expect
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('$entityClassName has to be instance of %s', EntityInterface::class));

        // Given
        $command = new DeleteEntity(Uuid::v7()->toString(), \stdClass::class);
        $handler = new DeleteEntityHandler(
            $this->createMock(DbClientInterface::class),
            $this->createMock(EventDispatcherInterface::class)
        );

        // When & Then
        $handler($command);
    }
}
