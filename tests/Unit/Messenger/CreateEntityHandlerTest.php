<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Messenger;

use Ifrost\DoctrineApiBundle\Entity\EntityInterface;
use Ifrost\DoctrineApiBundle\Event\BeforeCreateEvent;
use Ifrost\DoctrineApiBundle\Events;
use Ifrost\DoctrineApiBundle\Messenger\Command\CreateEntity;
use Ifrost\DoctrineApiBundle\Messenger\Handler\CreateEntityHandler;
use Ifrost\DoctrineApiBundle\Tests\Variant\Entity\Product;
use Ifrost\DoctrineApiBundle\Utility\DbClientInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CreateEntityHandlerTest extends TestCase
{
    public function testShouldThrowInvalidArgumentExceptionWhenGivenClassNameIsNotEntityInterface(): void
    {
        // Expect
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('$entityClassName has to be instance of %s', EntityInterface::class));

        // Given
        $command = new CreateEntity(Uuid::v7()->toString(), \stdClass::class, []);
        $handler = new CreateEntityHandler(
            $this->createMock(DbClientInterface::class),
            $this->createMock(EventDispatcherInterface::class)
        );

        // When & Then
        $handler($command);
    }

    public function testShouldDoNothingWhenEventDataIsEmpty(): void
    {
        // Expect & Given
        $dbClient = $this->createMock(DbClientInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $command = new CreateEntity(Uuid::v7()->toString(), Product::class, ['name' => 'Playstation 5', 'price' => 500, 'description' => 'Best console ever!']);
        $handler = new CreateEntityHandler($dbClient, $dispatcher);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(function (BeforeCreateEvent $event) {
                    $this->assertInstanceOf(Product::class, $event->entity);
                    $event->data = [];

                    return true;
                }),
                Events::BEFORE_CREATE
            );
        $dbClient->expects($this->never())->method('insert');

        // When & Then
        $handler($command);
    }
}
