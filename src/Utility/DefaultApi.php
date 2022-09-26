<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Utility;

use Doctrine\DBAL\Exception as DbalException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Ifrost\ApiBundle\Utility\ApiRequestInterface;
use Ifrost\DoctrineApiBundle\Entity\EntityInterface;
use Ifrost\DoctrineApiBundle\Exception\NotFoundException;
use Ifrost\DoctrineApiBundle\Exception\NotUniqueException;
use Ifrost\DoctrineApiBundle\Query\DbalCriteria;
use Ifrost\DoctrineApiBundle\Query\Entity\EntitiesQuery;
use Ifrost\DoctrineApiBundle\Query\Entity\EntityQuery;
use PlainDataTransformer\Transform;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultApi
{
    private string $entityClassName;

    public function __construct(
        string $entityClassName,
        private DbClient $db,
        private ApiRequestInterface $apiRequest,
    ) {
        $this->setEntityClassName($entityClassName);
    }

    public function find(): JsonResponse
    {
        $conditions = Transform::toArray($this->apiRequest->getField('conditions') ?? []);

        return new JsonResponse(
            $this->db->fetchAll(
                EntitiesQuery::class,
                $this->entityClassName::getTableName(),
                DbalCriteria::createFromArray([
                    'conditions' => $conditions,
                    'orderBy' => $this->apiRequest->getField('orderBy'),
                    'offset' => $this->apiRequest->getField('offset'),
                    'limit' => $this->apiRequest->getField('limit'),
                ])
            )
        );
    }

    /**
     * @throws \Exception
     */
    public function findOne(): JsonResponse
    {
        return new JsonResponse($this->db->fetchOne(
            EntityQuery::class,
            $this->entityClassName::getTableName(),
            $this->apiRequest->getAttribute('uuid', '')
        ));
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

        try {
            $this->db->insert(
                $this->entityClassName::getTableName(),
                $entity->jsonSerialize()
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
            $this->db->fetchOne(EntityQuery::class, $this->entityClassName::getTableName(), $uuid);
        } catch (NotFoundException) {
            throw new NotFoundException(sprintf('Record "%s" not found', $this->entityClassName), 404);
        }

        $data = $this->apiRequest->getRequest($this->entityClassName::getFields());
        $data['uuid'] = $uuid;
        $entity = $this->entityClassName::createFromArray($data);

        try {
            $this->db->update(
                $this->entityClassName::getTableName(),
                $entity->jsonSerialize(),
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
            $entity = $this->entityClassName::createFromArray([
                ...$this->db->fetchOne(EntityQuery::class, $this->entityClassName::getTableName(), $uuid),
                ...$this->apiRequest->getRequest($this->entityClassName::getFields(), false),
                'uuid' => $uuid,
            ]);
        } catch (NotFoundException) {
            throw new NotFoundException(sprintf('Record "%s" not found', $this->entityClassName), 404);
        }

        try {
            $this->db->update(
                $this->entityClassName::getTableName(),
                $entity->jsonSerialize(),
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
        $this->db->delete(
            $this->entityClassName::getTableName(),
            ['uuid' => $this->apiRequest->getAttribute('uuid', '')]
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
