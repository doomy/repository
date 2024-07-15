<?php

declare(strict_types=1);

namespace Doomy\Repository;

readonly class EntityFactory
{
    public function __construct(
        private RepoFactory $repoFactory,
    ) {
    }

    public function createEntity($entityClass, $values)
    {
        // TODO: avoid sending repofactory to entity instances
        return new $entityClass($values, $this->repoFactory);
    }
}
