<?php

declare(strict_types=1);

namespace Doomy\Repository;

use Doomy\CustomDibi\Connection;
use Doomy\Repository\Helper\DbHelper;

readonly class EntityFactory
{
    public function __construct(
        private Connection $connection,
        private RepoFactory $repoFactory,
        private DbHelper $dbHelper
    ) {
    }

    public function createEntity($entityClass, $values)
    {
        // TODO could this somehow be injected instead of creating new instances?
        return new $entityClass($values, new RepoFactory($this->connection, $this, $this->dbHelper));
    }
}
