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
    private ?int $intColumn;

    private string $varcharColumn;

    public function __construct(
        string $varcharColumn,
        ?int $intColumn = null,
        private readonly bool $enabled = false
    ) {
        $this->intColumn = $intColumn;
        $this->varcharColumn = $varcharColumn;
    }

    public function getIntColumn(): ?int
    {
        return $this->intColumn;
    }

    public function getVarcharColumn(): string
    {
        return $this->varcharColumn;
    }

    public function setVarcharColumn(string $varcharColumn): void
    {
        $this->varcharColumn = $varcharColumn;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}