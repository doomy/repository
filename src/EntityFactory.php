<?php

namespace Doomy\Repository;

class EntityFactory
{
    private $connection;

    /** @var RepoFactory */
    public $repoFactory;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function createEntity($entityClass, $values) {
        // TODO could this somehow be injected instead of creating new instances?
        return new $entityClass($values, new RepoFactory($this->connection, $this));
    }
}