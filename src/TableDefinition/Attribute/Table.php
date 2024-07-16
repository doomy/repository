<?php

declare(strict_types=1);

namespace Doomy\Repository\TableDefinition\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Table extends AbstractTableDefinitionAttribute
{
    public function __construct(
        private string $name
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }
}
