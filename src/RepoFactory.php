<?php

declare(strict_types=1);

namespace Doomy\Repository;

use Doomy\CustomDibi\Connection;
use Doomy\Repository\Helper\DbHelper;

class RepoFactory
{
    /**
     * @var Repository[] $repositories
     */
    private array $repositories;

    public function __construct(
        private readonly Connection $connection,
        private readonly EntityFactory $entityFactory,
        private readonly DbHelper $dbHelper
    ) {
    }

    /**
     * @param class-string $entityClass
     */
    public function getRepository(string $entityClass): Repository
    {
        if (! isset($this->repositories[$entityClass])) {
            $this->repositories[$entityClass] = new Repository(
                $entityClass,
                $this->connection,
                $this->entityFactory,
                $this->dbHelper
            );
        }
        return $this->repositories[$entityClass];
    }
}
