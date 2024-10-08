<?php

namespace Doomy\Repository\Tests;

use Doomy\Repository\TableDefinition\ColumnTypeMapper;
use Doomy\Repository\Tests\Support\TestEntity;
use Doomy\Repository\TableDefinition\TableDefinitionFactory;
use Doomy\Repository\Helper\DbHelper;
use Doomy\Repository\Tests\Support\TestEntityL3;
use PHPUnit\Framework\TestCase;

final class CreateTableCodeFromEntityTest extends TestCase
{
    public function testGetCreateCodeFromEntity(): void
    {
        $tableDefinitionFactory = new TableDefinitionFactory(new ColumnTypeMapper());
        $dbHelper = new DbHelper(new ColumnTypeMapper());

        $tableDefinition = $tableDefinitionFactory->createTableDefinition(TestEntity::class);

        $createCode = $dbHelper->getCreateTable($tableDefinition);
        $this->assertEquals(
            'CREATE TABLE test_table (intColumn INT NOT NULL AUTO_INCREMENT, varcharColumn VARCHAR(255) NOT NULL UNIQUE, enabled BOOLEAN NOT NULL, PRIMARY KEY(intColumn));',
            $createCode
        );
    }

    public function testGetCreateCodeFromExtendedEntity(): void
    {
        $tableDefinitionFactory = new TableDefinitionFactory(new ColumnTypeMapper());
        $dbHelper = new DbHelper(new ColumnTypeMapper());

        $tableDefinition = $tableDefinitionFactory->createTableDefinition(TestEntityL3::class);

        $createCode = $dbHelper->getCreateTable($tableDefinition);
        $this->assertEquals(
            'CREATE TABLE test_table (intColumn INT NOT NULL AUTO_INCREMENT, varcharColumn VARCHAR(255) NOT NULL UNIQUE, enabled BOOLEAN NOT NULL, PRIMARY KEY(intColumn));',
            $createCode
        );
    }

}