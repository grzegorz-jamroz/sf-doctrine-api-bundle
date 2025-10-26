<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Messenger\Handler;

use Ifrost\DoctrineApiBundle\Entity\EntityInterface;
use Ifrost\DoctrineApiBundle\Event\BeforeDeleteEvent;
use Ifrost\DoctrineApiBundle\Events;
use Ifrost\DoctrineApiBundle\Messenger\Command\DeleteEntity;
use Ifrost\DoctrineApiBundle\Utility\DbClientInterface;
use InvalidArgumentException;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DeleteEntityHandler
{
    public function __construct(
        private DbClientInterface $db,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function __invoke(DeleteEntity $command): void
    {
        $uuid = Uuid::fromString($command->getUuid());
        $entityClassName = $command->getEntityClassName();

        if (is_a($entityClassName, EntityInterface::class, true) === false) {
            throw new InvalidArgumentException(sprintf('$entityClassName has to be instance of %s', EntityInterface::class));
        }

        $event = new BeforeDeleteEvent($uuid->toString(), $entityClassName);
        $this->dispatcher->dispatch($event, Events::BEFORE_DELETE);
        $this->db->delete(
            $entityClassName::getTableName(),
            ['uuid' => $uuid->toBinary()],
        );
    }
}
