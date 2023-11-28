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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DoctrineApi implements ApiInterface
{
    private string $entityClassName;

    public function __construct(
        string $entityClassName,
        private DbClient $db,
        private ApiRequestInterface $apiRequest,
        private EventDispatcherInterface $dispatcher
    ) {
        $this->setEntityClassName($entityClassName);
    }

    public function find(array $options = []): JsonResponse
    {
        $conditions = Transform::toArray($this->apiRequest->getField('conditions') ?? []);
        $results = $this->db->fetchAll(
            EntitiesQuery::class,
            $this->entityClassName::getTableName(),
            DbalCriteria::createFromArray([
                'conditions' => $conditions,
                'orderBy' => $this->apiRequest->getField('orderBy'),
                'offset' => $this->apiRequest->getField('offset'),
                'limit' => $this->apiRequest->getField('limit'),
            ])
        );
        $event = new AfterFindEvent($this->entityClassName, $results);
        $this->dispatcher->dispatch($event, Events::AFTER_FIND);

        if (Transform::toBool($options['raw_data'] ?? false)) {
            return new JsonResponse($event->getData());
        }

        return new JsonResponse(
            array_map(
                function (array $result) {
                    $result = array_map(
                        function(mixed $value) {
                            if (is_string($value) === false) {
                                return $value;
                            }

                            try {
                                $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                            } catch (\Exception) {
                            }

                            return $value;
                        },
                        $result
                    );

                    return $this->entityClassName::createFromArray(
                        [
                            ...$result,
                            'uuid' => Uuid::fromBytes($result['uuid']),
                        ]
                    )->jsonSerialize();
                },
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
            return new JsonResponse($this->db->fetchOne(
                EntityQuery::class,
                $this->entityClassName::getTableName(),
                $this->apiRequest->getAttribute('uuid', '')
            ));
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
        $data['uuid'] ??= (string) Uuid::uuid4();
        $entity = $this->entityClassName::createFromArray($data);
        $event = new BeforeCreateEvent($entity, $entity->getWritableFormat());
        $this->dispatcher->dispatch($event, Events::BEFORE_CREATE);

        try {
            $this->db->insert(
                $this->entityClassName::getTableName(),
                $event->getData()
            );
        } catch (UniqueConstraintViolationException) {
            throw new NotUniqueException(sprintf('Unable to create "%s" due to not unique fields.', $this->entityClassName));
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
        $uuid = $this->apiRequest->getAttribute('uuid', '');
        try {
            $previousData = $this->db->fetchOne(EntityQuery::class, $this->entityClassName::getTableName(), $uuid);
        } catch (NotFoundException) {
            throw new NotFoundException(sprintf('Record "%s" not found', $this->entityClassName), 404);
        }

        $data = $this->apiRequest->getRequest($this->entityClassName::getFields());
        $data['uuid'] = $uuid;
        $entity = $this->entityClassName::createFromArray($data);
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
                ['uuid' => $uuid]
            );
        } catch (UniqueConstraintViolationException) {
            throw new NotUniqueException(sprintf('Unable to update "%s" due to not unique fields.', $this->entityClassName));
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
        $uuid = $this->apiRequest->getAttribute('uuid', '');

        try {
            $previousData = $this->db->fetchOne(EntityQuery::class, $this->entityClassName::getTableName(), $uuid);
            $entity = $this->entityClassName::createFromArray([
                ...$previousData,
                ...$this->apiRequest->getRequest($this->entityClassName::getFields(), false),
                'uuid' => $uuid,
            ]);
        } catch (NotFoundException) {
            throw new NotFoundException(sprintf('Record "%s" not found', $this->entityClassName), 404);
        }

        $event = new BeforeModifyEvent(
            $entity,
            $entity->getWritableFormat(),
            $previousData
        );
        $this->dispatcher->dispatch($event, Events::BEFORE_MODIFY);

        try {
            $this->db->update(
                $this->entityClassName::getTableName(),
                $entity->getWritableFormat(),
                ['uuid' => $uuid]
            );
        } catch (UniqueConstraintViolationException) {
            throw new NotUniqueException(sprintf('Unable to modify "%s" due to not unique fields.', $this->entityClassName));
        }

        return new JsonResponse($entity->jsonSerialize());
    }

    /**
     * @throws DbalException
     */
    public function delete(): JsonResponse
    {
        $uuid = $this->apiRequest->getAttribute('uuid', '');
        $event = new BeforeDeleteEvent($uuid);
        $this->dispatcher->dispatch($event, Events::BEFORE_DELETE);
        $this->db->delete(
            $this->entityClassName::getTableName(),
            ['uuid' => $uuid]
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
}
