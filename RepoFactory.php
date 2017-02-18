<?php

namespace Doomy\Repository;

use Doomy\Repository\Repository;
use Doomy\Repository\Model\EntityFactory;

class RepoFactory
{
    private $connection;
    private $entityFactory;
    private $repositories;

    public function __construct($connection, EntityFactory $entityFactory) {
        $this->connection = $connection;
        $this->entityFactory = $entityFactory;
    }

    public function getRepository($entityClass) {
        if (!isset($this->repositories[$entityClass]))
            $this->repositories[$entityClass] = new Repository($entityClass, $this->connection, $this->entityFactory);
        return $this->repositories[$entityClass];
    }
}