<?php

declare(strict_types=1);

namespace Doomy\Repository\Model;

readonly class TableDefinition
{
    /**
     * @param array<string, string> $columns
     */
    public function __construct(private string $tableName, private array $columns, private ?string $primaryKey = null)
    {
    }

    /**
     * @return array<string,string>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getPrimaryKey(): ?string
    {
        return $this->primaryKey;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }
}
