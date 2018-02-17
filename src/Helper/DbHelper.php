<?php


namespace Doomy\Repository\Helper;

use Doomy\Repository\Model\TableDefinition;

class DbHelper
{
    public static function translateWhere($where) {
        if (!$where) return "1 = 1";
        if (!is_array($where)) return $where
            ;

        $whereParts = [];
        foreach($where as $columnName => $expected) {
            if (!is_array(($expected))) {
                if ($expected[0] == "~") {
                    $whereParts[] = static::getLikeExpression($columnName, substr($expected, 1));
                }
                else if (is_null($expected)) {
                    $whereParts[] = "`$columnName` IS NULL";
                }
                else $whereParts[] = "`$columnName` = '$expected'";
            }
            else {
                foreach ($expected as &$expectedValue) {
                    $expectedValue = "'$expectedValue'"; // escape
                }
                $expectedCode = implode(", ", $expected);
                $whereParts[] = "$columnName IN ($expectedCode)";
            }
        }

        foreach ($whereParts as $key => $part) {
            if(is_null($part)) unset($whereParts[$key]);
        }

        return implode(" AND ", $whereParts);
    }

    public static function getMultiWhere($whereArray) {
        foreach ($whereArray as $key => $wherePart) {
            $whereArray[$key] = self::translateWhere($wherePart);
        }

        foreach ($whereArray as $key => $part) {
            if(!$part) unset($whereArray[$key]);
        }

        return implode(" AND ", $whereArray);
    }

    public static function getLikeExpression($columnName, $expected) {
        if (!$expected) return null;
        $expected = filter_var($expected, FILTER_SANITIZE_SPECIAL_CHARS);
        return "$columnName LIKE '%$expected%'";
    }

    public static function convertRowKeysToUppercase($row) {
        foreach ($row as $key => $value) {
            $uKey = strtoupper($key);
            $row[$uKey] = $value;
        }

        foreach ($row as $key => $value) {
            if (!ctype_lower($key)) continue;
            $lKey = strtolower($key);
            unset($row[$lKey]);
        }

        return $row;
    }

    public static function normalizeNameFromDB($name) {
        $name = str_replace("_", " ", $name);
        $name = ucfirst(strtolower($name));

        return $name;
    }

    public static function getCreateTable(TableDefinition $definition) {
        $definitionCode = static::getColumnsCode($definition->getColumns());

        if (!empty($definition->getPrimaryKey())) {
            $definitionCode .= ", PRIMARY KEY({$definition->getPrimaryKey()})";
        }

        return "CREATE TABLE {$definition->getTableName()} ($definitionCode);";
    }

    public static function getColumnsCode($columns) {
        $columnCodes = [];

        foreach ($columns as $columnName => $definition) {
            $columnCodes[] = "$columnName $definition";
        }

        return implode(", ", $columnCodes);
    }
}