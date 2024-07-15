<?php

declare(strict_types=1);

namespace Doomy\Repository\Model;

use Doomy\Helper\StringTools;
use Doomy\Repository\RepoFactory;
use Doomy\Repository\Repository;

abstract class Entity
{
    public const ?string TABLE = null;

    public const ?string VIEW = null;

    public const ?string SEQUENCE = null;

    public const string IDENTITY_COLUMN = 'ID';

    public const ?string PRIMARY_KEY = null;

    protected bool $created = false;

    /**
     * @var array<string, string>
     */
    protected array $columns = [];

    protected TableDefinition $tableDefinition;

    /**
     * @param array<string, mixed> $values
     * @param RepoFactory $repoFactory
     */
    public function __construct(array $values, private readonly RepoFactory $repoFactory)
    {
        $this->initColumns();

        foreach ($values as $key => $value) {
            $uKey = strtoupper($key);
            if (property_exists($this, $uKey)) {
                $this->{$uKey} = $value;
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function __toArray(): array
    {
        $array = [];
        $properties = call_user_func('get_object_vars', $this);
        foreach ($properties as $key => $value) {
            if (StringTools::isAllCaps($key)) {
                $array[$key] = $value;
            }
        }
        return $array;
    }

    public function __toString(): string
    {
        return '';
    }

    public function setCreated(bool $created): void
    {
        $this->created = $created;
    }

    public function wasCreated(): bool
    {
        return $this->created;
    }

    public function getTableDefinition(): ?TableDefinition
    {
        if (empty($this->columns) || empty(static::TABLE)) {
            return null;
        }

        if (! empty($this->tableDefinition)) {
            return $this->tableDefinition;
        }

        return $this->tableDefinition = new TableDefinition(static::TABLE, $this->columns, static::PRIMARY_KEY);
    }

    public function getIdentity(): ?string
    {
        return $this->{static::IDENTITY_COLUMN};
    }

    /**
     * @return mixed[]
     */
    public static function getFields(): array
    {
        $fields = [];
        foreach (get_class_vars(static::class) as $key => $default) {
            if (StringTools::isAllCaps($key)) {
                $fields[] = $key;
            }
        }
        return $fields;
    }

    /**
     * @param class-string $entityClass
     */
    protected function getRepository(string $entityClass): Repository
    {
        return $this->repoFactory->getRepository($entityClass);
    }

    /**
     * @param class-string $entityClass
     */
    protected function get11Relation(string $entityClass, string|int $entityId, ?string $propertyName = null): Entity
    {
        $repository = $this->repoFactory->getRepository($entityClass);
        if (! $propertyName) {
            return $repository->findById($entityId);
        }
        return $repository->findOne([
            $propertyName => $entityId,
        ]);
    }

    /**
     * @param class-string $entityClass
     * @param array<string, mixed> $where
     * @param mixed[]|string|null $orderBy
     * @return Entity[]
     */
    protected function get1NRelation(
        string $entityClass,
        string $propertyName,
        string|int|null $entityId = null,
        array $where = [],
        array|string|null $orderBy = null
    ): array
    {
        if ($entityId === null) {
            $entityId = $this->{static::IDENTITY_COLUMN};
        }
        $repository = $this->getRepository($entityClass);
        $where = array_merge($where, [
            $propertyName => $entityId,
        ]);
        return $repository->findAll($where, $orderBy);
    }

    private function initColumns(): void
    {
        foreach ($this->columns as $key => $definition) {
            $uKey = strtoupper($key);
            $this->{$uKey} = null;
        }
    }
}
