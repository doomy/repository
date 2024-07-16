<?php

declare(strict_types=1);

namespace Doomy\Repository;

use Dibi\Exception;
use Doomy\CustomDibi\Connection;
use Doomy\Repository\Helper\DbHelper;
use Doomy\Repository\Model\Entity;
use Doomy\Repository\TableDefinition\TableDefinitionFactory;

/**
 * @template T of Entity
 */
readonly class Repository
{
    private ?string $view;

    private ?string $table;

    private string $identityColumn;

    /**
     * @param class-string<T> $entityClass
     */
    public function __construct(
        private string $entityClass,
        private Connection $connection,
        private EntityFactory $entityFactory,
        private DbHelper $dbHelper,
        private TableDefinitionFactory $tableDefinitionFactory
    ) {
        $tableDefinition = $this->tableDefinitionFactory->createTableDefinition($entityClass);

        $this->table = $this->view = $tableDefinition->getTableName();

        if ($tableDefinition->getIdentityColumn() === null) {
            throw new \Exception('Identity column not found in table definition');
        }

        $this->identityColumn = $tableDefinition->getIdentityColumn()
            ->getName();
    }

    /**
     * @param string|array<string,mixed>|null $where
     * @return T[]
     */
    public function findAll(string|array|null $where = null, ?string $orderBy = null, ?int $limit = null): array
    {
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
            $entities[$row[$this->identityColumn]] = $this->entityFactory->createEntity($this->entityClass, $row);
        }
        return $entities;
    }

    /**
     * @param array<string, mixed>|string|null $where
     * @return T|null
     */
    public function findOne(array|string|null $where = null, ?string $orderBy = null): Entity|null
    {
        $all = $this->findAll($where, $orderBy, 1);
        return array_shift($all);
    }

    /**
     * @return T|null
     */
    public function findById(string|int $id): ?Entity
    {
        /** @var T|null $entity */
        $entity = $this->findBy($this->identityColumn, $id);
        return $entity;
    }

    /**
     * @return T|false
     */
    public function findBy(string $name, string|int $value): Entity|false
    {
        $q = "SELECT * FROM {$this->view} WHERE {$name}='{$value}'";
        $result = $this->connection->query($q);
        $all = $result->fetchAll();
        $values = array_shift($all);
        if ($values === null) {
            return false;
        }

        $entity = $this->entityFactory->createEntity($this->entityClass, $values);
        return $values ? $entity : false;
    }

    /**
     * @param array<string, mixed> $values
     */
    public function add(array $values): mixed
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
        if (isset($values[$this->identityColumn]) && $values[$this->identityColumn]) {
            /** @var int|string $id */
            $id = $values[$this->identityColumn];
            $entity = $this->findById($id);
        }

        if (isset($entity)) {
            $this->update($entity->{$this->identityColumn}, $values);
            return $this->entityFactory->createEntity($this->entityClass, $values);
        }
        $newId = $this->add($values);
        $values[$this->identityColumn] = $newId;
        return $this->entityFactory->createEntity($this->entityClass, $values);
    }

    public function deleteById(int|string $id): void
    {
        $this->delete([
            $this->identityColumn => $id,
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
}
