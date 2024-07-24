<?php

declare(strict_types=1);

namespace Doomy\Repository;

use Dibi\Row;
use Doomy\Repository\Model\Entity;
use Doomy\Repository\TableDefinition\ColumnType;
use Doomy\Repository\TableDefinition\TableDefinitionFactory;

final readonly class EntityFactory
{
    public function __construct(
        private readonly TableDefinitionFactory $tableDefinitionFactory
    ) {
    }

    /**
     * @template T of Entity
     * @param class-string<T> $entityClass
     * @param array<string,mixed>|Row $values
     * @return T
     */
    public function createEntity(string $entityClass, array|Row $values): Entity
    {
        return new $entityClass(...$this->prepareValues($entityClass, $values));
    }

    /**
     * @param class-string<Entity> $entityClass
     * @param array<string,mixed>|Row $values
     * @return array<string,mixed>
     */
    private function prepareValues(string $entityClass, array|Row $values): array
    {
        if ($values instanceof Row) {
            $values = $values->toArray();
        }

        $tableDefinition = $this->tableDefinitionFactory->createTableDefinition($entityClass);
        foreach ($tableDefinition->getColumns() as $column) {
            if (($column->getColumnType() === ColumnType::BOOLEAN)) {
                $values[$column->getName()] = (bool) $values[$column->getName()];
            }
        }

        return $values;
    }
}
