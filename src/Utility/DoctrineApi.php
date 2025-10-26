<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Utility;

use Doctrine\DBAL\Exception as DbalException;
use Ifrost\ApiBundle\Messenger\MessageHandlerInterface;
use Ifrost\ApiBundle\Utility\ApiRequestInterface;
use Ifrost\ApiFoundation\ApiInterface;
use Ifrost\DoctrineApiBundle\Entity\EntityInterface;
use Ifrost\DoctrineApiBundle\Event\AfterFindEvent;
use Ifrost\DoctrineApiBundle\Events;
use Ifrost\DoctrineApiBundle\Exception\NotFoundException;
use Ifrost\DoctrineApiBundle\Exception\NotUniqueException;
use Ifrost\DoctrineApiBundle\Messenger\Command\CreateEntity;
use Ifrost\DoctrineApiBundle\Messenger\Command\DeleteEntity;
use Ifrost\DoctrineApiBundle\Messenger\Command\ModifyEntity;
use Ifrost\DoctrineApiBundle\Messenger\Command\UpdateEntity;
use Ifrost\DoctrineApiBundle\Query\DbalCriteria;
use Ifrost\DoctrineApiBundle\Query\Entity\EntitiesQuery;
use Ifrost\DoctrineApiBundle\Query\Entity\EntityQuery;
use InvalidArgumentException;
use PlainDataTransformer\Transform;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DoctrineApi implements ApiInterface
{
    private string $entityClassName;

    public function __construct(
        string $entityClassName,
        private DbClientInterface $db,
        private ApiRequestInterface $apiRequest,
        private MessageHandlerInterface $messageHandler,
        private EventDispatcherInterface $dispatcher,
    ) {
        $this->setEntityClassName($entityClassName);
    }

    /**
     * @param array<string,mixed> $options
     *
     * @throws DbalException
     */
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
            ]),
        );

        if (Transform::toBool($options['raw_data'] ?? false)) {
            $records = array_map(
                fn ($data) => [
                    ...$data,
                    'uuid' => Uuid::fromBinary(
                        Transform::toString($data['uuid'])
                    )->toString(),
                ],
                $records,
            );
        }

        /** @var array<int, array<string, string|int|bool|float|null>> $records */
        $event = new AfterFindEvent($this->entityClassName, $records);
        $this->dispatcher->dispatch($event, Events::AFTER_FIND);

        if (Transform::toBool($options['raw_data'] ?? false)) {
            return new JsonResponse($event->data);
        }

        return new JsonResponse(
            array_map(
                fn (array $record) => $this->getEntityDataFromRecord($record),
                $event->data,
            ),
        );
    }

    /**
     * @throws NotFoundException
     * @throws DbalException
     */
    public function findOne(): JsonResponse
    {
        try {
            $uuid = Transform::toString($this->apiRequest->getAttribute('uuid'));
            /** @var array<string, string|int|bool|float|null> $record */
            $record = $this->db->fetchOne(
                EntityQuery::class,
                $this->entityClassName::getTableName(),
                Uuid::fromString($uuid)->toBinary(),
            );

            return new JsonResponse(
                $this->getEntityDataFromRecord($record),
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
        $uuid = Transform::toString(
            $this->apiRequest->getField(
                'uuid',
                Uuid::v7()->toString()
            )
        );
        $this->messageHandler->handle(
            new CreateEntity(
                $uuid,
                $this->entityClassName,
                $this->getRequestData(),
            ),
        );

        return new JsonResponse(['uuid' => $uuid]);
    }

    /**
     * @throws DbalException
     * @throws NotFoundException
     * @throws NotUniqueException
     */
    public function update(): JsonResponse
    {
        $this->messageHandler->handle(
            new UpdateEntity(
                Transform::toString($this->apiRequest->getRequiredAttribute('uuid')),
                $this->entityClassName,
                $this->getRequestData(),
            ),
        );

        return new JsonResponse();
    }

    /**
     * @throws DbalException
     * @throws NotFoundException
     * @throws NotUniqueException
     */
    public function modify(): JsonResponse
    {
        $this->messageHandler->handle(
            new ModifyEntity(
                Transform::toString($this->apiRequest->getRequiredAttribute('uuid')),
                $this->entityClassName,
                $this->getRequestData(),
            ),
        );

        return new JsonResponse();
    }

    /**
     * @throws DbalException
     */
    public function delete(): JsonResponse
    {
        $this->messageHandler->handle(
            new DeleteEntity(
                Transform::toString($this->apiRequest->getRequiredAttribute('uuid')),
                $this->entityClassName,
            ),
        );

        return new JsonResponse();
    }

    private function setEntityClassName(string $entityClassName): void
    {
        if (!in_array(EntityInterface::class, Transform::toArray(class_implements($entityClassName)))) {
            throw new InvalidArgumentException(sprintf('Given argument entityClassName (%s) has to implement "%s" interface.', $entityClassName, EntityInterface::class));
        }

        $this->entityClassName = $entityClassName;
    }

    /**
     * @param array<string, mixed> $record
     */
    private function getEntityFromRecord(array $record): EntityInterface
    {
        /** @var EntityInterface $entity */
        $entity = $this->entityClassName::createFromArray(
            [
                ...array_map(
                    fn (mixed $value) => TransformRecord::toRead($value),
                    $record,
                ),
                'uuid' => Uuid::fromBinary(
                    Transform::toString($record['uuid'])
                ),
            ],
        );

        return $entity;
    }

    /**
     * @param array<string, string|int|bool|float|null> $record
     *
     * @return array<string, mixed>
     */
    private function getEntityDataFromRecord(array $record): array
    {
        return $this->getEntityFromRecord($record)->jsonSerialize();
    }

    /**
     * @return array<string, string|int|bool|float|array<string, mixed>|null>
     */
    private function getRequestData(): array
    {
        $data = array_filter(
            $this->apiRequest->getData(),
            fn (mixed $value) => is_string($value) || is_int($value) || is_bool($value) || is_float($value) || is_null($value) || is_array($value),
        );

        /** @var array<string, string|int|bool|float|array<string, mixed>|null> $data */
        return $data;
    }
}
