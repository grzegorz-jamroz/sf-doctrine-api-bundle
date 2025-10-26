<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Messenger;

use Ifrost\DoctrineApiBundle\Entity\EntityInterface;
use Ifrost\DoctrineApiBundle\Event\BeforeUpdateEvent;
use Ifrost\DoctrineApiBundle\Events;
use Ifrost\DoctrineApiBundle\Messenger\Command\UpdateEntity;
use Ifrost\DoctrineApiBundle\Messenger\Handler\UpdateEntityHandler;
use Ifrost\DoctrineApiBundle\Tests\Variant\Entity\Product;
use Ifrost\DoctrineApiBundle\Utility\DbClientInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UpdateEntityHandlerTest extends TestCase
{
    public function testShouldThrowInvalidArgumentExceptionWhenGivenClassNameIsNotEntityInterface(): void
    {
        // Expect
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('$entityClassName has to be instance of %s', EntityInterface::class));

        // Given
        $command = new UpdateEntity(Uuid::v7()->toString(), \stdClass::class, []);
        $handler = new UpdateEntityHandler(
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
        $command = new UpdateEntity(Uuid::v7()->toString(), Product::class, ['name' => 'Playstation 5', 'price' => 500, 'description' => 'Best console ever!']);
        $handler = new UpdateEntityHandler($dbClient, $dispatcher);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(function (BeforeUpdateEvent $event) {
                    $this->assertInstanceOf(Product::class, $event->entity);
                    $event->data = [];

                    return true;
                }),
                Events::BEFORE_UPDATE
            );
        $dbClient->expects($this->never())->method('update');

        // When & Then
        $handler($command);
    }
}
