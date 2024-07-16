<?php

namespace Doomy\Repository\Tests\Support;

use Doomy\Repository\Model\Entity;
use Doomy\Repository\TableDefinition\Attribute\Column\Identity;
use Doomy\Repository\TableDefinition\Attribute\Column\PrimaryKey;
use Doomy\Repository\TableDefinition\Attribute\Table;

#[Table('test_table')]
final class TestEntity extends Entity {
    #[PrimaryKey]
    #[Identity]
    private int $intColumn;

    private string $varcharColumn;
}