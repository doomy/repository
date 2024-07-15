<?php

declare(strict_types=1);

namespace Doomy\Repository\Helper;

use Dibi\Row;
use Doomy\Repository\Model\TableDefinition;

final readonly class DbHelper
{
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
                    $likeExpected = substr($expected, 1);
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

    /**
     * @param mixed[] $row
     * @return mixed[]
     */
    public static function convertRowKeysToUppercase(array|Row $row): array
    {
        foreach ($row as $key => $value) {
            $uKey = strtoupper($key);
            $row[$uKey] = $value;
        }

        foreach ($row as $key => $value) {
            if (! ctype_lower($key)) {
                continue;
            }
            $lKey = strtolower($key);
            unset($row[$lKey]);
        }

        return $row instanceof Row ? $row->toArray() : $row;
    }

    public static function normalizeNameFromDB(string $name): string
    {
        $name = str_replace('_', ' ', $name);
        $name = ucfirst(strtolower($name));

        return $name;
    }

    public static function getCreateTable(TableDefinition $definition): string
    {
        $definitionCode = static::getColumnsCode($definition->getColumns());

        if (! empty($definition->getPrimaryKey())) {
            $definitionCode .= ", PRIMARY KEY({$definition->getPrimaryKey()})";
        }

        return "CREATE TABLE {$definition->getTableName()} ({$definitionCode});";
    }

    /**
     * @param array<string, string> $columns
     */
    public static function getColumnsCode(array $columns): string
    {
        $columnCodes = [];

        foreach ($columns as $columnName => $definition) {
            $columnCodes[] = "{$columnName} {$definition}";
        }

        return implode(', ', $columnCodes);
    }

    private function escapeSingleQuote(string $string): string
    {
        return str_replace("'", "''", $string);
    }
}
