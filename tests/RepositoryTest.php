<?php

namespace Doomy\Repository\Tests;
use Dibi\Exception;
use Doomy\CustomDibi\Connection;
use Doomy\Repository\EntityFactory;
use Doomy\Repository\Helper\DbHelper;
use Doomy\Repository\RepoFactory;
use Doomy\Repository\TableDefinition\ColumnTypeMapper;
use Doomy\Repository\TableDefinition\TableDefinitionFactory;
use Doomy\Repository\Tests\Support\TestEntity;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class RepositoryTest extends TestCase
{
    private Connection $connection;

    public function __construct(string $name)
    {
        $config = json_decode(file_get_contents(__DIR__ . '/../testingDbCredentials.json'), true);

        $this->connection = new Connection($config);

        parent::__construct($name);
    }

    public function testRepository(): void
    {
        $tableDefinitionFactory = new TableDefinitionFactory(new ColumnTypeMapper());
        $dbHelper = new DbHelper(new ColumnTypeMapper());
        $entityFactory = new EntityFactory();
        $repoFactory = new RepoFactory($this->connection, $entityFactory, $dbHelper, $tableDefinitionFactory);
        $repository = $repoFactory->getRepository(TestEntity::class);

        $tableDefinition = $tableDefinitionFactory->createTableDefinition(TestEntity::class);
        $createCode = $dbHelper->getCreateTable($tableDefinition);

        $this->connection->query($createCode);

        $entity1 = new TestEntity(intColumn: 1, varcharColumn: 'test1');
        $entity2 = new TestEntity(intColumn: 2, varcharColumn: 'test2');
        $entity3 = new TestEntity(varcharColumn: 'test3');

        $repository->save($entity1);
        $repository->save($entity2);
        $repository->save($entity3);

        $foundAll = $repository->findAll();
        Assert::assertCount(3, $foundAll);

        $foundEntity1 = array_shift($foundAll);
        Assert::assertEquals(1, $foundEntity1->getIntColumn());
        Assert::assertEquals('test1', $foundEntity1->getVarcharColumn());

        $foundEntity2 = array_shift($foundAll);
        Assert::assertEquals(2, $foundEntity2->getIntColumn());
        Assert::assertEquals('test2', $foundEntity2->getVarcharColumn());

        $foundEntity3 = array_shift($foundAll);
        Assert::assertEquals(3, $foundEntity3->getIntColumn()); // testing auto_increment
        Assert::assertEquals('test3', $foundEntity3->getVarcharColumn());


        $entitiesFiltered = $repository->findAll(['intColumn' => 1]);
        Assert::assertCount(1, $entitiesFiltered);

        $entitiesFilteredByLike = $repository->findAll(['varcharColumn' => "~test"]);
        Assert::assertCount(3, $entitiesFilteredByLike);

        $entityFoundByVarcharColumn = $repository->findOne(['varcharColumn' => 'test2']);
        Assert::assertEquals(2, $entityFoundByVarcharColumn->getIntColumn());

        $entityFoundById = $repository->findById(3);
        Assert::assertEquals('test3', $entityFoundById->getVarcharColumn());

        $entityFoundById->setVarcharColumn('test 3 updated');
        $repository->save($entityFoundById);
        $entityUpdated = $repository->findById(3);
        Assert::assertEquals('test 3 updated', $entityUpdated->getVarcharColumn());

        $repository->deleteById(3);
        Assert::assertCount(2, $repository->findAll());

        $repository->delete('intColumn = 2');
        Assert::assertCount(1, $repository->findAll());
    }

    public function tearDown(): void
    {
        $this->connection->query('DROP TABLE test_table');
    }

}