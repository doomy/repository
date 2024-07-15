<?php

declare(strict_types=1);

namespace Doomy\Repository;

use Dibi\Exception;
use Doomy\CustomDibi\Connection;
use Doomy\Repository\Helper\DbHelper;

class Repository
{
    private $view;

    private $table;

    private $sequence;

    private $identityColumn;

    public function __construct(
        private readonly string $entityClass,
        private readonly Connection $connection,
        private readonly EntityFactory $entityFactory,
        private readonly DbHelper $dbHelper
    ) {
        $this->table = $entityClass::TABLE;
        $this->view = $entityClass::VIEW ? $entityClass::VIEW : $entityClass::TABLE;
        $this->identityColumn = $entityClass::IDENTITY_COLUMN;
        $this->sequence = $entityClass::SEQUENCE;

        if ((! $this->connection->tableExists($entityClass::TABLE)) && ! empty($entityClass::getTableDefinition())) {
            $this->connection->query($this->dbHelper->getCreateTable($entityClass::getTableDefinition()));
        }
    }

    public function findAll($where = null, $orderBy = null, $limit = null)
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
            $row = $this->dbHelper->convertRowKeysToUppercase($row);
            $entities[$row[$this->identityColumn]] = $this->entityFactory->createEntity($this->entityClass, $row);
        }
        return $entities;
    }

    public function findOne($where = null, $orderBy = null)
    {
        $all = $this->findAll($where, $orderBy, 1);
        return array_shift($all);
    }

    public function findById($id)
    {
        if ($id === null) {
            return false;
        }
        return $this->findBy($this->identityColumn, $id);
    }

    public function findBy($name, $value)
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
     * @return int|string|null
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

            return null;
        }
    }

    public function update($id, $values)
    {
        $this->connection->query("UPDATE {$this->table} SET ", $values, "WHERE {$this->identityColumn} = '{$id}'");
    }

    public function save($values)
    {
        $values = $this->prepareValues($values);

        if (isset($values[$this->identityColumn]) && $values[$this->identityColumn]) {
            $entity = $this->findById($values[$this->identityColumn]);
        }

        if (isset($entity) && $entity) {
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

    public function getNextId()
    {
        $result = $this->connection->query("SELECT {$this->sequence}.nextval FROM DUAL");
        return $result->fetchSingle();
    }

    public function deleteById($id)
    {
        $entityClass = $this->entityClass;
        $this->delete([
            $entityClass::IDENTITY_COLUMN => $id,
        ]);
    }

    public function delete($where)
    {
        $where = $this->dbHelper->translateWhere($where);
        $this->connection->query("DELETE FROM {$this->table} WHERE {$where}");
    }

    private function isDatabaseProperty($name)
    {
        return $name === strtoupper($name);
    }

    private function prepareValues($values)
    {
        if (! is_array($values)) {
            $values = (array) $values;
        }
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
