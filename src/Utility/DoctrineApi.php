<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Utility;

use Doctrine\DBAL\Exception as DbalException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Ifrost\ApiBundle\Utility\ApiRequestInterface;
use Ifrost\ApiFoundation\ApiInterface;
use Ifrost\DoctrineApiBundle\Entity\EntityInterface;
use Ifrost\DoctrineApiBundle\Event\AfterFindEvent;
use Ifrost\DoctrineApiBundle\Event\BeforeCreateEvent;
use Ifrost\DoctrineApiBundle\Event\BeforeDeleteEvent;
use Ifrost\DoctrineApiBundle\Event\BeforeModifyEvent;
use Ifrost\DoctrineApiBundle\Event\BeforeUpdateEvent;
use Ifrost\DoctrineApiBundle\Events;
use Ifrost\DoctrineApiBundle\Exception\NotFoundException;
use Ifrost\DoctrineApiBundle\Exception\NotUniqueException;
use Ifrost\DoctrineApiBundle\Query\DbalCriteria;
use Ifrost\DoctrineApiBundle\Query\Entity\EntitiesQuery;
use Ifrost\DoctrineApiBundle\Query\Entity\EntityQuery;
use PlainDataTransformer\Transform;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DoctrineApi implements ApiInterface
{
    private string $entityClassName;

    public function __construct(
        string $entityClassName,
        private DbClientInterface $db,
        private ApiRequestInterface $apiRequest,
        private EventDispatcherInterface $dispatcher
    ) {
        $this->setEntityClassName($entityClassName);
    }

    public function find(array $options = []): JsonResponse
    {
        $conditions = Transform::toArray($this->apiRequest->getField('conditions') ?? []);
        $records = $this->db->fetchAll(
            EntitiesQuery::class,
            $this->entityClassName::getTableName(),
            DbalCriteria::createFromArray([
                'conditions' => $conditions,
                'orderBy' => $this->apiRequest->getField('orderBy'),
                'offset' => $this->apiRequest->getField('offset'),
                'limit' => $this->apiRequest->getField('limit'),
            ])
        );
        $event = new AfterFindEvent($this->entityClassName, $records);
        $this->dispatcher->dispatch($event, Events::AFTER_FIND);

        if (Transform::toBool($options['raw_data'] ?? false)) {
            return new JsonResponse($event->getData());
        }

        return new JsonResponse(
            array_map(
                fn (array $record) => $this->getEntityDataFromRecord($record),
                $event->getData()
            )
        );
    }

    /**
     * @throws NotFoundException
     * @throws DbalException
     */
    public function findOne(): JsonResponse
    {
        try {
            return new JsonResponse(
                $this->getEntityDataFromRecord(
                    $this->db->fetchOne(
                        EntityQuery::class,
                        $this->entityClassName::getTableName(),
                        Uuid::fromString($this->apiRequest->getAttribute('uuid', ''))->getBytes()
                    )
                )
            );
        } catch (NotFoundException) {
            throw new NotFoundException(sprintf('Record "%s" not found', $this->entityClassName), 404);
        }
    }

    /**
     * @throws DbalException
     * @throws NotUniqueException
     */
    public function create(): JsonResponse
    {
        $data = $this->apiRequest->getRequest($this->entityClassName::getFields());

        try {
            $data['uuid'] = Uuid::fromString($data['uuid']);
        } catch (\Throwable) {
            unset($data['uuid']);
        }

        $entity = $this->entityClassName::createFromRequest($data);

        $event = new BeforeCreateEvent($entity, $entity->getWritableFormat());
        $this->dispatcher->dispatch($event, Events::BEFORE_CREATE);

        try {
            $this->db->insert(
                $this->entityClassName::getTableName(),
                $event->getData()
            );
        } catch (UniqueConstraintViolationException) {
            throw new NotUniqueException(sprintf('Unable to create "%s" due to not unique fields.', $this->entityClassName), 409);
        }

        return new JsonResponse($entity->jsonSerialize());
    }

    /**
     * @throws DbalException
     * @throws NotFoundException
     * @throws NotUniqueException
     */
    public function update(): JsonResponse
    {
        $uuid = Uuid::fromString($this->apiRequest->getAttribute('uuid', ''));
        $previousData = $this->fetchOne($uuid);
        $entity = $this->entityClassName::createFromRequest(
            [
                ...$this->apiRequest->getRequest($this->entityClassName::getFields()),
                'uuid' => $uuid,
            ]
        );
        $event = new BeforeUpdateEvent(
            $entity,
            $entity->getWritableFormat(),
            $previousData
        );
        $this->dispatcher->dispatch($event, Events::BEFORE_UPDATE);

        try {
            $this->db->update(
                $this->entityClassName::getTableName(),
                $event->getData(),
                ['uuid' => $uuid->getBytes()]
            );
        } catch (UniqueConstraintViolationException) {
            throw new NotUniqueException(sprintf('Unable to update "%s" due to not unique fields.', $this->entityClassName), 409);
        }

        return new JsonResponse($entity->jsonSerialize());
    }

    /**
     * @throws DbalException
     * @throws NotFoundException
     * @throws NotUniqueException
     */
    public function modify(): JsonResponse
    {
        $uuid = Uuid::fromString($this->apiRequest->getAttribute('uuid', ''));
        $previousData = $this->fetchOne($uuid);
        $entity = $this->entityClassName::createFromRequest([
            ...$this->entityClassName::createFromArray([...$previousData, 'uuid' => $uuid])->jsonSerialize(),
            ...$this->apiRequest->getRequest($this->entityClassName::getFields(), false),
            'uuid' => $uuid,
        ]);
        $event = new BeforeModifyEvent(
            $entity,
            $entity->getWritableFormat(),
            $previousData
        );
        $this->dispatcher->dispatch($event, Events::BEFORE_MODIFY);

        try {
            $this->db->update(
                $this->entityClassName::getTableName(),
                $event->getData(),
                ['uuid' => $uuid->getBytes()]
            );
        } catch (UniqueConstraintViolationException) {
            throw new NotUniqueException(sprintf('Unable to modify "%s" due to not unique fields.', $this->entityClassName), 409);
        }

        return new JsonResponse($entity->jsonSerialize());
    }

    /**
     * @throws DbalException
     */
    public function delete(): JsonResponse
    {
        $uuid = Uuid::fromString($this->apiRequest->getAttribute('uuid', ''));
        $event = new BeforeDeleteEvent($uuid->toString());
        $this->dispatcher->dispatch($event, Events::BEFORE_DELETE);
        $this->db->delete(
            $this->entityClassName::getTableName(),
            ['uuid' => $uuid->getBytes()]
        );

        return new JsonResponse();
    }

    private function setEntityClassName(string $entityClassName): void
    {
        if (!in_array(EntityInterface::class, Transform::toArray(class_implements($entityClassName)))) {
            throw new \InvalidArgumentException(sprintf('Given argument entityClassName (%s) has to implement "%s" interface.', $entityClassName, EntityInterface::class));
        }

        $this->entityClassName = $entityClassName;
    }

    private function getEntityDataFromRecord(array $record): array
    {
        return $this->entityClassName::createFromArray(
            [
                ...array_map(
                    fn (mixed $value) => TransformRecord::toRead($value),
                    $record
                ),
                'uuid' => Uuid::fromBytes($record['uuid']),
            ]
        )->jsonSerialize();
    }

    private function fetchOne(UuidInterface $uuid): array
    {
        try {
            return $this->db->fetchOne(
                EntityQuery::class,
                $this->entityClassName::getTableName(),
                $uuid->getBytes(),
            );
        } catch (NotFoundException) {
            throw new NotFoundException(sprintf('Record "%s" not found', $this->entityClassName), 404);
        }
    }
}
