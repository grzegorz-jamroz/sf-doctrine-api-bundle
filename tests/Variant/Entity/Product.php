<?php

namespace Ifrost\DoctrineApiBundle\Tests\Variant\Entity;

use Ifrost\DoctrineApiBundle\Entity\EntityInterface;
use PlainDataTransformer\Transform;
use PlainDataTransformer\TransformNumeric;
use Symfony\Component\Uid\Uuid;

class Product implements EntityInterface
{
    public function __construct(
        private Uuid $uuid,
        private string $name,
        private string $code,
        private string $description,
        private int $rate,
        private array $tags = [],
    ) {
    }

    public function getUuid(): Uuid
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

    public function getRate(): int
    {
        return $this->rate;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public static function getTableName(): string
    {
        return 'product';
    }

    /**
     * @return array<int, string>
     */
    public static function getFields(): array
    {
        return array_keys(self::createFromArray([])->jsonSerialize());
    }

    public function getWritableFormat(): array
    {
        return [
            ...$this->jsonSerialize(),
            'uuid' => $this->uuid->toBinary(),
            'tags' => json_encode($this->tags),
            'rate' => $this->rate,
        ];
    }

    public function jsonSerialize(): array
    {
        return [
            'uuid' => (string) $this->uuid,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'rate' => TransformNumeric::toFloat($this->rate, 2),
            'tags' => $this->tags,
        ];
    }

    public static function createFromArray(array $data): static|self
    {
        $uuid = $data['uuid'] ?? Uuid::v7();

        return new self(
            is_string($uuid) ? Uuid::fromString($uuid) : $uuid,
            Transform::toString($data['name'] ?? ''),
            Transform::toString($data['code'] ?? ''),
            Transform::toString($data['description'] ?? ''),
            Transform::toInt($data['rate'] ?? 0),
            Transform::toArray($data['tags'] ?? []),
        );
    }

    public static function createFromRequest(array $data): static|self
    {
        $uuid = $data['uuid'] ?? Uuid::v7();

        return new self(
            is_string($uuid) ? Uuid::fromString($uuid) : $uuid,
            Transform::toString($data['name'] ?? ''),
            Transform::toString($data['code'] ?? ''),
            Transform::toString($data['description'] ?? ''),
            TransformNumeric::toInt($data['rate'] ?? 0, 2),
            Transform::toArray($data['tags'] ?? []),
        );
    }
}
