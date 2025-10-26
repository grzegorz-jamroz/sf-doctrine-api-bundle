<?php
declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Messenger\Handler;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Ifrost\DoctrineApiBundle\Entity\EntityInterface;
use Ifrost\DoctrineApiBundle\Event\BeforeModifyEvent;
use Ifrost\DoctrineApiBundle\Events;
use Ifrost\DoctrineApiBundle\Exception\NotUniqueException;
use Ifrost\DoctrineApiBundle\Messenger\Command\ModifyEntity;
use Ifrost\DoctrineApiBundle\Utility\DbClientInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ModifyEntityHandler
{
    public function __construct(
        private DbClientInterface $db,
        private EventDispatcherInterface $dispatcher
    )
    {
    }

    public function __invoke(ModifyEntity $command): void
    {
        $uuid = Uuid::fromString($command->getUuid());
        $entityClassName = $command->getEntityClassName();

        if (is_a($entityClassName, EntityInterface::class, true) === false) {
            throw new \InvalidArgumentException(sprintf('$entityClassName has to be instance of %s', EntityInterface::class));
        }

        $data = $this->getData($command);
        $keys = array_keys($data);
        $entity = $entityClassName::createFromRequest([
            ...$data,
            'uuid' => $uuid,
        ]);
        $writableData = array_filter(
            $entity->getWritableFormat(),
            fn (string $key) => in_array($key, $keys),
            ARRAY_FILTER_USE_KEY,
        );
        $event = new BeforeModifyEvent($entity, $writableData);
        $this->dispatcher->dispatch($event, Events::BEFORE_MODIFY);

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
            throw new NotUniqueException(sprintf('Unable to modify "%s" due to not unique fields.', $entityClassName), 409);
        }
    }

    private function getData(ModifyEntity $command): array
    {
        $entityClassName = $command->getEntityClassName();
        $entityFields = $entityClassName::getFields();

        return array_filter(
            $command->getData(),
            fn($key) => in_array($key, $entityFields),
            ARRAY_FILTER_USE_KEY
        );
    }
}
