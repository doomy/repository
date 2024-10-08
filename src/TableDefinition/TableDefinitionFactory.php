<?php

declare(strict_types=1);

namespace Doomy\Repository\TableDefinition;

use Doomy\Repository\Model\Entity;
use Doomy\Repository\TableDefinition\Attribute\AbstractTableDefinitionAttribute;
use Doomy\Repository\TableDefinition\Attribute\Column\AbstractTableDefinitionPropertyAttribute;
use Doomy\Repository\TableDefinition\Attribute\Column\Identity;
use Doomy\Repository\TableDefinition\Attribute\Column\PrimaryKey;
use Doomy\Repository\TableDefinition\Attribute\Column\Unique;
use Doomy\Repository\TableDefinition\Attribute\Table;

final readonly class TableDefinitionFactory
{
    public function __construct(
        private ColumnTypeMapper $columnTypeMapper
    ) {
    }

    /**
     * @template T of Entity
     * @param class-string<T> $entityClass
     */
    public function createTableDefinition(string $entityClass): TableDefinition
    {
        $classReflection = new \ReflectionClass($entityClass);
        $attributes = $this->getClassAttributes($classReflection);
        $properties = $this->getHierarchyProperties($classReflection);
        $columns = $this->createColumnsFromProperties($properties);

        $primaryKey = $identity = null;
        foreach ($columns as $column) {
            if ($column->isPrimaryKey()) {
                $primaryKey = $column;
            }
            if ($column->isIdentity()) {
                $identity = $column;
            }
        }

        return new TableDefinition(
            tableName: $this->getTableName($attributes),
            columns: $columns,
            primaryKey: $primaryKey,
            identityColumn: $identity
        );
    }

    /**
     * @param \ReflectionProperty[] $properties
     * @return Column[]
     */
    private function createColumnsFromProperties(array $properties): array
    {
        $columns = [];
        foreach ($properties as $property) {
            $attributes = $this->getPropertyAttributes($property);
            $isPrimaryKey = false;
            $isIdentity = false;
            $isUnique = false;
            foreach ($attributes as $attribute) {
                if ($attribute instanceof PrimaryKey) {
                    $isPrimaryKey = true;
                } elseif ($attribute instanceof Identity) {
                    $isIdentity = true;
                } elseif ($attribute instanceof Unique) {
                    $isUnique = true;
                }
            }

            $columns[] = new Column(
                name: $property->getName(),
                columnType: $this->columnTypeMapper->mapFromReflectionProperty($property),
                length: null,
                isPrimaryKey: $isPrimaryKey,
                isIdentity: $isIdentity,
                isUnique: $isUnique
            );
        }
        return $columns;
    }

    /**
     * @template T of Entity
     * @param \ReflectionClass<T> $reflectionClass
     * @return AbstractTableDefinitionAttribute[]
     */
    private function getClassAttributes(\ReflectionClass $reflectionClass): array
    {
        $classAttributeReflections = $reflectionClass->getAttributes();

        $attributes = [];
        foreach ($classAttributeReflections as $classAttributeReflection) {
            $instance = $classAttributeReflection->newInstance();
            if ($instance instanceof AbstractTableDefinitionAttribute) {
                $attributes[] = $instance;
            }
        }

        if ($reflectionClass->getParentClass() !== false) {
            $attributes = array_merge($attributes, $this->getClassAttributes($reflectionClass->getParentClass()));
        }

        return $attributes;
    }

    /**
     * @return AbstractTableDefinitionPropertyAttribute[]
     */
    private function getPropertyAttributes(\ReflectionProperty $reflectionProperty): array
    {
        $propertyAttributeReflections = $reflectionProperty->getAttributes();

        $attributes = [];
        foreach ($propertyAttributeReflections as $propertyAttributeReflection) {
            $instance = $propertyAttributeReflection->newInstance();
            if ($instance instanceof AbstractTableDefinitionPropertyAttribute) {
                $attributes[] = $instance;
            }
        }

        return $attributes;
    }

    /**
     * @param AbstractTableDefinitionAttribute[] $attributes
     */
    private function getTableName(array $attributes): string
    {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof Table) {
                return $attribute->getName();
            }
        }
        throw new \Exception('Table name not found');
    }

    /**
     * @template T of Entity
     * @param \ReflectionClass<T> $reflectionClass
     * @return \ReflectionProperty[]
     */
    private function getHierarchyProperties(\ReflectionClass $reflectionClass): array
    {
        $properties = $reflectionClass->getProperties();
        if ($reflectionClass->getParentClass() !== false) {
            $properties = array_merge($properties, $this->getHierarchyProperties($reflectionClass->getParentClass()));
        }
        return $properties;
    }
}
