<?php

declare(strict_types=1);

namespace Doomy\Repository;

use Dibi\Exception;
use Doomy\CustomDibi\Connection;
use Doomy\Repository\Helper\DbHelper;
use Doomy\Repository\Model\Entity;

/**
 * @template T of Entity
 */
readonly class Repository
{
    private ?string $view;

    private ?string $table;

    private ?string $sequence;

    private string $identityColumn;

    /**
     * @param class-string<T> $entityClass
     */
    public function __construct(
        private string $entityClass,
        private Connection $connection,
        private EntityFactory $entityFactory,
        private DbHelper $dbHelper
    ) {
        $this->table = $entityClass::TABLE;
        $this->view = $entityClass::VIEW ? $entityClass::VIEW : $entityClass::TABLE;
        $this->identityColumn = $entityClass::IDENTITY_COLUMN;
        $this->sequence = $entityClass::SEQUENCE;

        if ((! $this->connection->tableExists($entityClass::TABLE)) && ! empty($entityClass::getTableDefinition())) {
            $this->connection->query($this->dbHelper->getCreateTable($entityClass::getTableDefinition()));
        }
    }

    /**
     * @param string|array<string,mixed>|null $where
     * @param string|array<string,mixed>|null $orderBy
     * @return T[]
     */
    public function findAll(
        string|array|null $where = null,
        string|array|null $orderBy = null,
        ?int $limit = null
    ): array {
        $where = $this->dbHelper->translateWhere($where);
        $orderBy = $orderBy ? $orderBy : "{$this->identityColumn} ASC";
        $sql = "SELECT * FROM {$this->view} WHERE {$where} ORDER BY {$orderBy}";
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        $result = $this->connection->query($sql);
        $rows = $result->fetchAll();
        $entities = [];
        foreach ($rows as $row) {
            $row = $this->dbHelper->convertRowKeysToUppercase($row);
            $entities[$row[$this->identityColumn]] = $this->entityFactory->createEntity($this->entityClass, $row);
        }
        return $entities;
    }

    /**
     * @param array<string, mixed>|string|null $where
     * @param array<string, mixed>|string|null $orderBy
     * @return T|null
     */
    public function findOne(array|string|null $where = null, array|string|null $orderBy = null): Entity|null
    {
        $all = $this->findAll($where, $orderBy, 1);
        return array_shift($all);
    }

    /**
     * @return T|null
     */
    public function findById(int|string $id): ?Entity
    {
        return $this->findBy($this->identityColumn, $id);
    }

    /**
     * @return T|false
     */
    public function findBy(string $name, int|string $value): Entity|false
    {
        $q = "SELECT * FROM {$this->view} WHERE {$name}='{$value}'";
        $result = $this->connection->query($q);
        $all = $result->fetchAll();
        $values = array_shift($all);
        $entity = $this->entityFactory->createEntity($this->entityClass, $values);
        return $values ? $entity : false;
    }

    /**
     * @param array<string, mixed> $values
     */
    public function add(array $values): int|null
    {
        $this->connection->query("INSERT INTO {$this->table}", $values);
        try {
            return $this->connection->getInsertId();
        } catch (Exception) {
            if (isset($values[$this->identityColumn])) {
                return $values[$this->identityColumn];
            }
        }

        return null;
    }

    /**
     * @param array<string,mixed> $values
     */
    public function update(int|string $id, array $values): void
    {
        $this->connection->query("UPDATE {$this->table} SET ", $values, "WHERE {$this->identityColumn} = '{$id}'");
    }

    /**
     * @param array<string, mixed> $values
     * @return T
     */
    public function save(array $values): Entity
    {
        $values = $this->prepareValues($values);

        if (isset($values[$this->identityColumn]) && $values[$this->identityColumn]) {
            $entity = $this->findById($values[$this->identityColumn]);
        }

        if (isset($entity)) {
            $this->update($entity->{$this->identityColumn}, $values);
            return $this->entityFactory->createEntity($this->entityClass, $values);
        }

        // TODO: why was this here? We should allow identity column override
        // unset($values[$this->identityColumn]);
        $newId = $this->add($values);
        $values[$this->identityColumn] = $newId;
        $entity = $this->entityFactory->createEntity($this->entityClass, $values);
        $entity->setCreated(true);
        return $entity;

    }

    public function getNextId(): mixed
    {
        $result = $this->connection->query("SELECT {$this->sequence}.nextval FROM DUAL");
        return $result->fetchSingle();
    }

    public function deleteById(int|string $id): void
    {
        $entityClass = $this->entityClass;
        $this->delete([
            $entityClass::IDENTITY_COLUMN => $id,
        ]);
    }

    /**
     * @param array<string, mixed>|string $where
     */
    public function delete(array|string $where): void
    {
        $where = $this->dbHelper->translateWhere($where);
        $this->connection->query("DELETE FROM {$this->table} WHERE {$where}");
    }

    private function isDatabaseProperty(string $name): bool
    {
        return $name === strtoupper($name);
    }

    /**
     * @param array<string,mixed> $values
     * @return array<string, mixed>
     */
    private function prepareValues(array $values)
    {
        foreach ($values as $key => $value) {
            if (! $this->isDatabaseProperty($key)) {
                unset($values[$key]);
            }
            /* elseif($value instanceof \DateTime)
                 $values[$key] = date_format($value, DateConfig::DB_DATE_FORMAT); */
        }

        return $values;
    }
}
