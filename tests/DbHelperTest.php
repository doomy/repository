<?php

namespace Doomy\Repository\Tests;

use Doomy\Repository\Helper\DbHelper;
use Doomy\Repository\TableDefinition\Column;
use Doomy\Repository\TableDefinition\ColumnType;
use Doomy\Repository\TableDefinition\ColumnTypeMapper;
use Doomy\Repository\TableDefinition\TableDefinition;
use PHPUnit\Framework\TestCase;

final class DbHelperTest extends TestCase
{
    public function testGetCreateTableCode(): void
    {
        $columnTypeMapper = new ColumnTypeMapper();
        $dbHelper = new DbHelper($columnTypeMapper);
        $pkColumn = new Column(
            name: 'intColumn',
            columnType: ColumnType::INTEGER,
            length: null,
            isPrimaryKey: true,
            isIdentity: true
        );
        $varcharColumn = new Column('varcharColumn', ColumnType::VARCHAR, 255);
        $tableDefinition = new TableDefinition('test_table', [
            $pkColumn, $varcharColumn
        ], $pkColumn, $pkColumn);

        $createTableCode = $dbHelper->getCreateTable($tableDefinition);

        $this->assertEquals(
            'CREATE TABLE test_table (intColumn INT NOT NULL AUTO_INCREMENT, varcharColumn VARCHAR(255) NOT NULL, PRIMARY KEY(intColumn));',
            $createTableCode
        );
    }

}