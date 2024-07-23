<?php

declare(strict_types=1);

namespace Doomy\Repository\TableDefinition;

final class ColumnTypeMapper
{
    public function mapToMysqlString(ColumnType $columnType): string
    {
        return match ($columnType) {
            ColumnType::INTEGER => 'INT',
            ColumnType::VARCHAR => 'VARCHAR',
            ColumnType::TEXT => 'TEXT',
            ColumnType::TIMESTAMP => 'TIMESTAMP',
            ColumnType::BOOLEAN => 'BOOLEAN',
            ColumnType::FLOAT => 'FLOAT',
        };
    }

    public function mapFromReflectionProperty(\ReflectionProperty $property): ColumnType
    {
        $type = $property->getType();
        if ($type === null) {
            throw new \InvalidArgumentException('Property ' . $property->getName() . ' must have a type');
        }

        if ($type instanceof \ReflectionNamedType) {
            if ($type->isBuiltin()) {
                return match ($type->getName()) {
                    'int' => ColumnType::INTEGER,
                    'string' => ColumnType::VARCHAR,
                    'bool' => ColumnType::BOOLEAN,
                    'float' => ColumnType::FLOAT,
                    default => throw new \InvalidArgumentException('Unsupported type ' . $type->getName()),
                };
            }

            if ($type instanceof \ReflectionNamedType) {
                return match ($type->getName()) {
                    'DateTime' => ColumnType::TIMESTAMP,
                    'DateTimeInterface' => ColumnType::TIMESTAMP,
                    default => throw new \InvalidArgumentException('Unsupported type ' . $type->getName()),
                };
            }
        }

        throw new \InvalidArgumentException('Unsupported type.');
    }
}
