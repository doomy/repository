<?php

declare(strict_types=1);

namespace Doomy\Repository;

use Doomy\CustomDibi\Connection;
use Doomy\Repository\Helper\DbHelper;
use Doomy\Repository\Model\Entity;
use Doomy\Repository\TableDefinition\TableDefinitionFactory;

class RepoFactory
{
    /**
     * @var array<class-string<Entity>, Repository<Entity>>
     */
    private array $repositories;

    public function __construct(
        private readonly Connection $connection,
        private readonly EntityFactory $entityFactory,
        private readonly DbHelper $dbHelper,
        private readonly TableDefinitionFactory $tableDefinitionFactory
    ) {
    }

    /**
     * @template T of Entity
     * @param class-string<T> $entityClass
     * @return Repository<T>
     */
    public function getRepository(string $entityClass): Repository
    {
        if (! isset($this->repositories[$entityClass])) {
            $this->repositories[$entityClass] = new Repository(
                $entityClass,
                $this->connection,
                $this->entityFactory,
                $this->dbHelper,
                $this->tableDefinitionFactory
            );
        }

        /** @var Repository<T> $repository */
        $repository = $this->repositories[$entityClass];
        return $repository;
    }
}
