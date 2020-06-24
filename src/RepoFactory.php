<?php

namespace Doomy\Repository;

use Doomy\CustomDibi\Connection;

class RepoFactory
{
    private $connection;
    private $entityFactory;
    private $repositories;

    public function __construct(Connection $connection, EntityFactory $entityFactory) {
        $this->connection = $connection;
        $this->entityFactory = $entityFactory;
    }

    public function getRepository($entityClass) {
        if (!isset($this->repositories[$entityClass]))
            $this->repositories[$entityClass] = new Repository($entityClass, $this->connection, $this->entityFactory);
        return $this->repositories[$entityClass];
    }
}
