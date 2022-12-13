<?php

namespace Ifrost\DoctrineApiBundle\Tests\Variant\Entity;

use Ifrost\DoctrineApiBundle\Entity\EntityInterface;
use PlainDataTransformer\Transform;

class Product implements EntityInterface
{
    public const TABLE = 'product';

    public function __construct(
        private string $uuid,
        private string $code,
        private string $name,
        private string $description,
        private array $tags = [],
    ) {
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public static function getTableName(): string
    {
        return self::TABLE;
    }

    /**
     * @return array<int, string>
     */
    public static function getFields(): array
    {
        return array_keys(self::createFromArray([])->jsonSerialize());
    }

    public static function createFromArray(array $data): static|self
    {
        return new self(
            Transform::toString($data['uuid'] ?? ''),
            Transform::toString($data['code'] ?? ''),
            Transform::toString($data['name'] ?? ''),
            Transform::toString($data['description'] ?? ''),
            Transform::toArray($data['tags'] ?? []),
        );
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return [
            'uuid' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'tags' => $this->tags,
        ];
    }

    public function getWritableFormat(): array
    {
        return [
            ...$this->jsonSerialize(),
            'tags' => json_encode($this->tags),
        ];
    }
}
