<?php

namespace Doomy\Repository\Tests;

use Doomy\Repository\TableDefinition\Attribute\Column\Identity;
use Doomy\Repository\TableDefinition\Attribute\Column\PrimaryKey;
use Doomy\Repository\TableDefinition\Attribute\Table;
use Doomy\Repository\TableDefinition\TableDefinitionFactory;
use PHPUnit\Framework\TestCase;
use Doomy\Repository\Model\Entity;
use Doomy\Repository\Tests\Support\TestEntity;
use Doomy\Repository\TableDefinition\ColumnTypeMapper;
use Doomy\Repository\TableDefinition\ColumnType;

final class TableDefinitionFactoryTest extends TestCase
{
    public function testCreateTableDefinition(): void
    {
        $columnTypeMapper = new ColumnTypeMapper();
        $tableDefinitionFactory = new TableDefinitionFactory($columnTypeMapper);

        $tableDefinition = $tableDefinitionFactory->createTableDefinition(TestEntity::class);
        $columns = $tableDefinition->getColumns();

        $this->assertEquals('test_table', $tableDefinition->getTableName());
        $this->assertCount(3, $columns);

        $this->assertEquals('intColumn', $columns[0]->getName());
        $this->assertEquals(ColumnType::INTEGER, $columns[0]->getColumnType());
        $this->assertEquals(null, $columns[0]->getLength());
        $this->assertTrue($columns[0]->isPrimaryKey());
        $this->assertTrue($columns[0]->isIdentity());
        $this->assertFalse($columns[0]->isUnique());

        $this->assertEquals('varcharColumn', $columns[1]->getName());
        $this->assertEquals(ColumnType::VARCHAR, $columns[1]->getColumnType());
        $this->assertEquals(255, $columns[1]->getLength());
        $this->assertFalse($columns[1]->isPrimaryKey());
        $this->assertFalse($columns[1]->isIdentity());
        $this->assertTrue($columns[1]->isUnique());
    }
}

