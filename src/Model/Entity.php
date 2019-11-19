<?php

namespace Doomy\Repository\Model;

use Doomy\Helper\StringTools;
use Doomy\Repository\RepoFactory;

abstract class Entity
{
    const TABLE = NULL;
    const VIEW = NULL;
    const SEQUENCE = NULL;
    const IDENTITY_COLUMN = 'ID';
    const PRIMARY_KEY = NULL;

    protected $created = false;
    private $repoFactory;
    protected static $columns = [];
    protected static $tableDefinition;

    public function __construct($values, RepoFactory $repoFactory)
    {
        if (!$values) return false;

        $this->initColumns();

        foreach ($values as $key => $value) {
            $uKey = strtoupper($key);
            if (property_exists($this, $uKey)) {
                $this->{$uKey} = $value;
            }
        }

        $this->repoFactory = $repoFactory;
    }

    public function setCreated($bool)
    {
        $this->created = $bool;
    }

    public function wasCreated()
    {
        return $this->created;
    }

    public static function getTableDefinition() {
        if (empty(static::$columns) || empty(static::TABLE)) return NULL;

        if (!empty(static::$tableDefinition)) return static::$tableDefinition;

        return static::$tableDefinition = new TableDefinition(static::TABLE, static::$columns, static::PRIMARY_KEY);
    }

    public function getIdentity() {
        return $this->{static::IDENTITY_COLUMN};
    }

    protected function getRepository($entityClass)
    {
        return $this->repoFactory->getRepository($entityClass);
    }

    protected function get11Relation($entityClass, $entityId, $propertyName = null)
    {
        $repository = $this->repoFactory->getRepository($entityClass);
        if (!$propertyName)
            return $repository->findById($entityId);
        else return $repository->findOne([$propertyName => $entityId]);
    }

    protected function get1NRelation($entityClass, $propertyName, $entityId = null, $where = [], $orderBy = null)
    {
        if (is_null($entityId)) $entityId = $this->{static::IDENTITY_COLUMN};
        $repository = $this->getRepository($entityClass);
        $where = array_merge($where, [$propertyName => $entityId]);
        return $repository->findAll($where, $orderBy);
    }

    private function initColumns() {
        foreach(static::$columns as $key => $definition) {
            $uKey = strtoupper($key);
            $this->$uKey = NULL;
        }
    }

    public function __toArray(){
        $array = [];
        $properties = call_user_func('get_object_vars', $this);
        foreach ($properties as $key => $value) {
            if (StringTools::isAllCaps($key)) {
                $array[$key] = $value;
            }
        }
        return $array;
    }

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


    public function __toString()
    {
        return '';
    }
}