<?php

declare(strict_types=1);

namespace Doomy\Repository\TableDefinition;

final class Column
{
    private const INT DEFAULT_VARCHAR_LENGTH = 255;

    public function __construct(
        private readonly string $name,
        private readonly ColumnType $columnType,
        private ?int $length = null,
        private bool $isPrimaryKey = false,
        private bool $isIdentity = false,
        private bool $nullable = false,
    ) {
        if ($columnType === ColumnType::VARCHAR && $length === null) {
            $this->length = self::DEFAULT_VARCHAR_LENGTH;
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getColumnType(): ColumnType
    {
        return $this->columnType;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function isPrimaryKey(): bool
    {
        return $this->isPrimaryKey;
    }

    public function isIdentity(): bool
    {
        return $this->isIdentity;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }
}
