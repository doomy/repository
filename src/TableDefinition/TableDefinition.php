<?php

declare(strict_types=1);

namespace Doomy\Repository\TableDefinition;

readonly class TableDefinition
{
    /**
     * @param Column[] $columns
     */
    public function __construct(
        private string $tableName,
        private array $columns,
        private ?Column $primaryKey = null,
        private ?Column $identityColumn = null,
    ) {
    }

    /**
     * @return Column[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getPrimaryKey(): ?Column
    {
        return $this->primaryKey;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getIdentityColumn(): ?Column
    {
        return $this->identityColumn;
    }
}
