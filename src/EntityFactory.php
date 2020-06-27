<?php

namespace Doomy\Repository;

use Doomy\CustomDibi\Connection;

class EntityFactory
{
    private Connection $connection;

    /** @var RepoFactory */
    public $repoFactory;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    public function createEntity($entityClass, $values) {
        // TODO could this somehow be injected instead of creating new instances?
        return new $entityClass($values, new RepoFactory($this->connection, $this));
    }
}
