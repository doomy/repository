<?php

declare(strict_types=1);

namespace Doomy\Repository\Helper;

use Doomy\Repository\TableDefinition\Column;
use Doomy\Repository\TableDefinition\ColumnType;
use Doomy\Repository\TableDefinition\ColumnTypeMapper;
use Doomy\Repository\TableDefinition\TableDefinition;

final readonly class DbHelper
{
    public function __construct(
        private ColumnTypeMapper $columnTypeMapper
    ) {
    }

    /**
     * @param array<string, mixed>|string|null $where
     */
    public function translateWhere(array|string|null $where): string
    {
        if (! $where) {
            return '1 = 1';
        }
        if (! is_array($where)) {
            return $where
            ;
        }

        $whereParts = [];
        foreach ($where as $columnName => $expected) {
            if (is_string(($expected))) {
                if (is_string($expected) && ($expected[0] === '~')) {
                    $whereParts[] = static::getLikeExpression(
                        $columnName,
                        $this->escapeSingleQuote(substr($expected, 1))
                    );
                } elseif (is_string($expected)) {
                    $escapedExpected = $this->escapeSingleQuote($expected);
                    $whereParts[] = "`{$columnName}` = '{$escapedExpected}'";
                }
            } elseif ($expected === null) {
                $whereParts[] = "`{$columnName}` IS NULL";
            } elseif (is_array($expected)) {
                foreach ($expected as &$expectedValue) {
                    $expectedValueEscaped = $this->escapeSingleQuote($expectedValue);
                    $expectedValue = "'{$expectedValueEscaped}'"; // escape
                }
                $expectedCode = implode(', ', $expected);
                $whereParts[] = "{$columnName} IN ({$expectedCode})";
            } else {
                throw new \LogicException('Unexpected where condition format');
            }
        }

        foreach ($whereParts as $key => $part) {
            if ($part === null) {
                unset($whereParts[$key]);
            }
        }

        return implode(' AND ', $whereParts);
    }

    /**
     * @param array<string, string> $whereArray
     */
    public function getMultiWhere(array $whereArray): string
    {
        foreach ($whereArray as $key => $wherePart) {
            $whereArray[$key] = $this->translateWhere($wherePart);
        }

        foreach ($whereArray as $key => $part) {
            if (! $part) {
                unset($whereArray[$key]);
            }
        }

        return implode(' AND ', $whereArray);
    }

    public static function getLikeExpression(string $columnName, ?string $expected): ?string
    {
        if (! $expected) {
            return null;
        }
        $expected = filter_var($expected, FILTER_SANITIZE_SPECIAL_CHARS);
        return "{$columnName} LIKE '%{$expected}%'";
    }

    public function getCreateTable(TableDefinition $definition): string
    {
        $definitionCode = static::getColumnsCode($definition->getColumns());

        if (! empty($definition->getPrimaryKey())) {
            $definitionCode .= ", PRIMARY KEY({$definition->getPrimaryKey()
                ->getName()})";
        }

        return "CREATE TABLE {$definition->getTableName()} ({$definitionCode});";
    }

    /**
     * @param Column[] $columns
     */
    public function getColumnsCode(array $columns): string
    {
        $columnCodes = [];

        foreach ($columns as $column) {
            $typeTranslated = $this->columnTypeMapper->mapToMysqlString($column->getColumnType());

            if ($column->getLength() !== null) {
                $columnDefinition = "{$typeTranslated}({$column->getLength()})";
            } else {
                $columnDefinition = $typeTranslated;
            }

            if ($column->isIdentity() && $column->getColumnType() === ColumnType::INTEGER) {
                $columnDefinition .= ' NOT NULL AUTO_INCREMENT';
            } elseif (! $column->isNullable()) {
                $columnDefinition .= ' NOT NULL';
            } else {
                $columnDefinition .= ' NULL';
            }

            $columnCodes[] = "{$column->getName()} {$columnDefinition}";
        }

        return implode(', ', $columnCodes);
    }

    private function escapeSingleQuote(string $string): string
    {
        return str_replace("'", "''", $string);
    }
}
