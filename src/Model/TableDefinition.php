<?php

namespace Doomy\Repository\Model;

class TableDefinition
{
    private $columns;
    private $primaryKey;
    private $tableName;


    public function __construct($tableName, $columns, $primaryKey = NULL)
    {
        $this->tableName = $tableName;
        $this->columns = $columns;
        $this->primaryKey = $primaryKey;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function getTableName() {
        return $this->tableName;
    }
}