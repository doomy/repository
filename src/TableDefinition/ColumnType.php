<?php

declare(strict_types=1);

namespace Doomy\Repository\TableDefinition;

enum ColumnType
{
    case INTEGER;
    case VARCHAR;
    case TEXT;
    case TIMESTAMP;
    case BOOLEAN;
    case FLOAT;
}
