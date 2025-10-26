<?php
declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Messenger\Handler;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Ifrost\DoctrineApiBundle\Entity\EntityInterface;
use Ifrost\DoctrineApiBundle\Event\BeforeCreateEvent;
use Ifrost\DoctrineApiBundle\Events;
use Ifrost\DoctrineApiBundle\Exception\NotUniqueException;
use Ifrost\DoctrineApiBundle\Messenger\Command\CreateEntity;
use Ifrost\DoctrineApiBundle\Utility\DbClientInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CreateEntityHandler
{
    public function __construct(
        private DbClientInterface $db,
        private EventDispatcherInterface $dispatcher,
    )
    {
    }

    public function __invoke(CreateEntity $command): void
    {
        $uuid = Uuid::fromString($command->getUuid());
        $entityClassName = $command->getEntityClassName();

        if (is_a($entityClassName, EntityInterface::class, true) === false) {
            throw new \InvalidArgumentException(sprintf('$entityClassName has to be instance of %s', EntityInterface::class));
        }

        $data = $this->getData($command);
        $data['uuid'] = $uuid;
        $entity = $entityClassName::createFromRequest($data);
        $event = new BeforeCreateEvent($entity, $entity->getWritableFormat());
        $this->dispatcher->dispatch($event, Events::BEFORE_CREATE);

        if ($event->getData() === []) {
            return;
        }

        try {
            $this->db->insert(
                $entityClassName::getTableName(),
                $event->getData(),
            );
        } catch (UniqueConstraintViolationException) {
            throw new NotUniqueException(sprintf('Unable to create "%s" due to not unique fields.', $entityClassName), 409);
        }
    }

    private function getData(CreateEntity $command): array
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
