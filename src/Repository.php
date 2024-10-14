<?php

declare(strict_types=1);

namespace Doomy\Repository;

use Dibi\Connection;
use Dibi\Exception;
use Doomy\Repository\Helper\DbHelper;
use Doomy\Repository\Model\Entity;
use Doomy\Repository\TableDefinition\Column;
use Doomy\Repository\TableDefinition\ColumnType;
use Doomy\Repository\TableDefinition\TableDefinition;
use Doomy\Repository\TableDefinition\TableDefinitionFactory;

/**
 * @template T of Entity
 */
readonly class Repository
{
    private ?string $view;

    private ?string $table;

    private string $identityColumn;

    private TableDefinition $tableDefinition;

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
        $this->tableDefinition = $this->tableDefinitionFactory->createTableDefinition($entityClass);

        $this->table = $this->view = $this->tableDefinition->getTableName();

        if ($this->tableDefinition->getIdentityColumn() === null) {
            throw new \Exception('Identity column not found in table definition');
        }

        $this->identityColumn = $this->tableDefinition->getIdentityColumn()
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
        return $this->findBy($this->identityColumn, $id);
    }

    /**
     * @param T $entity
     * @return T
     */
    public function save(Entity $entity): Entity
    {
        if ($entity instanceof Entity) {
            $values = $this->convertEntityToValues($entity);
        }

        $id = $values[$this->identityColumn] ?? null;
        $existingEntity = is_string($id) || is_int($id) ? $this->findById($id) : null;

        if ($existingEntity !== null) {
            $this->update($existingEntity->{$this->getIdentityColumnGetter()}(), $values);
            return $entity;
        }

        $newId = $this->add($values);

        // check if we can set the original entity ID. If yes, we return the original reference. Otherwise, a clone is returned with the new ID
        $identitySetterMethod = $this->getIdentityColumnSetter();
        if (method_exists($entity, $identitySetterMethod) && (new \ReflectionMethod(
            $entity,
            $identitySetterMethod
        ))->isPublic()) {
            $entity->{$identitySetterMethod}($newId);
            return $entity;
        }

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

    /**
     * @return T|null
     */
    private function findBy(string $name, string|int $value): ?Entity
    {
        $q = "SELECT * FROM {$this->view} WHERE {$name}='{$value}'";
        $result = $this->connection->query($q);
        $all = $result->fetchAll();
        $values = array_shift($all);
        if ($values === null) {
            return null;
        }

        $entity = $this->entityFactory->createEntity($this->entityClass, $values);
        return $values ? $entity : null;
    }

    /**
     * @param array<string, mixed> $values
     */
    private function add(array $values): mixed
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
    private function update(int|string $id, array $values): void
    {
        $this->connection->query("UPDATE {$this->table} SET ", $values, "WHERE {$this->identityColumn} = '{$id}'");
    }

    /**
     * @return array<string, mixed>
     */
    private function convertEntityToValues(Entity $entity): array
    {
        $values = [];
        foreach ($this->tableDefinition->getColumns() as $column) {

            $values[$column->getName()] = $entity->{$this->getGetterName($column)}();
        }
        return $values;
    }

    private function getIdentityColumnGetter(): string
    {
        return 'get' . ucfirst($this->identityColumn);
    }

    private function getIdentityColumnSetter(): string
    {
        return 'set' . ucfirst($this->identityColumn);
    }

    private function getGetterName(Column $column): string
    {
        $possibleGetters = $this->getPossibleGetters($column);
        foreach ($possibleGetters as $getter) {
            if (method_exists($this->entityClass, $getter)) {
                return $getter;
            }
        }
        throw new \Exception("Getter for column {$column->getName()} not found in entity {$this->entityClass}");
    }

    /**
     * @return string[]
     */
    private function getPossibleGetters(Column $column): array
    {
        return match ($column->getColumnType()) {
            ColumnType::BOOLEAN => [
                'get' . ucfirst($column->getName()),
                'is' . ucfirst($column->getName()),
                'has' . ucfirst($column->getName()),
            ],
            default => ['get' . ucfirst($column->getName())],
        };
    }
}
