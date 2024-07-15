<?php

declare(strict_types=1);

namespace Doomy\Repository;

use Dibi\Row;
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
    public function createEntity(string $entityClass, array|Row $values): Entity
    {
        if ($values instanceof Row) {
            $values = $values->toArray();
        }

        return new $entityClass($values);
    }
}
