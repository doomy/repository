<?php

declare(strict_types=1);

namespace Doomy\Repository;

use Doomy\Repository\Model\Entity;

readonly class EntityFactory
{
    public function __construct(
    ) {
    }

    /**
     * @template T of Entity
     * @param class-string<T> $entityClass
     * @param array<string,mixed> $values
     * @return T
     */
    public function createEntity(string $entityClass, array $values): Entity
    {
        // TODO: avoid sending repofactory to entity instances
        return new $entityClass($values);
    }
}
