<?php

declare(strict_types=1);

namespace Doomy\Repository\TableDefinition\Attribute\Column;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class PrimaryKey extends AbstractTableDefinitionPropertyAttribute
{
}
