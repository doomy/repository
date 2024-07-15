<?php

declare(strict_types=1);

namespace Doomy\Repository;

use Doomy\Repository\Model\Entity;

readonly class EntityFactory
{
    public function __construct(
        private RepoFactory $repoFactory,
    ) {
    }

    /**
     * @param class-string $entityClass
     * @param array<string,mixed> $values
     * @return Entity
     */
    public function createEntity(string $entityClass, array $values): Entity
    {
        // TODO: avoid sending repofactory to entity instances
        return new $entityClass($values, $this->repoFactory);
    }
}
