<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Messenger\Handler;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Ifrost\DoctrineApiBundle\Entity\EntityInterface;
use Ifrost\DoctrineApiBundle\Event\BeforeUpdateEvent;
use Ifrost\DoctrineApiBundle\Events;
use Ifrost\DoctrineApiBundle\Exception\NotUniqueException;
use Ifrost\DoctrineApiBundle\Messenger\Command\UpdateEntity;
use Ifrost\DoctrineApiBundle\Utility\DbClientInterface;
use InvalidArgumentException;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UpdateEntityHandler
{
    public function __construct(
        private DbClientInterface $db,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function __invoke(UpdateEntity $command): void
    {
        $uuid = Uuid::fromString($command->uuid);
        $entityClassName = $command->entityClassName;

        if (is_a($entityClassName, EntityInterface::class, true) === false) {
            throw new InvalidArgumentException(sprintf('$entityClassName has to be instance of %s', EntityInterface::class));
        }

        $data = $this->getData($command);
        /** @var EntityInterface $entity */
        $entity = $entityClassName::createFromRequest(
            [
                ...$data,
                'uuid' => $uuid,
            ],
        );

        $event = new BeforeUpdateEvent($entity, $entity->getWritableFormat());
        $this->dispatcher->dispatch($event, Events::BEFORE_UPDATE);

        if ($event->data === []) {
            return;
        }

        try {
            $this->db->update(
                $entityClassName::getTableName(),
                $event->data,
                ['uuid' => $uuid->toBinary()],
            );
        } catch (UniqueConstraintViolationException) {
            throw new NotUniqueException(sprintf('Unable to update "%s" due to not unique fields.', $entityClassName), 409);
        }
    }

    /**
     * @return array<string, string|int|bool|float|array<mixed,mixed>|null>
     */
    private function getData(UpdateEntity $command): array
    {
        $entityClassName = $command->entityClassName;
        /** @var array<int, string> $entityFields */
        $entityFields = $entityClassName::getFields();

        return array_filter(
            $command->data,
            fn (string $key) => in_array($key, $entityFields),
            ARRAY_FILTER_USE_KEY
        );
    }
}
