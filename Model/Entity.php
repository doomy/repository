<?php

namespace Doomy\Repository\Model;

use Doomy\Repository\RepoFactory;

abstract class Entity
{
    const TABLE = NULL;
    const VIEW = NULL;
    const SEQUENCE = NULL;
    const IDENTITY_COLUMN = 'ID';
    protected $created = false;
    private $repoFactory;

    public function __construct($values, RepoFactory $repoFactory)
    {
        if (!$values) return false;

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
}