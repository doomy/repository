<?php

declare(strict_types=1);

namespace Doomy\Repository\Model;

use Doomy\Helper\StringTools;

abstract class Entity
{
    public const ?string TABLE = null;

    public const ?string VIEW = null;

    public const ?string SEQUENCE = null;

    public const string IDENTITY_COLUMN = 'ID';

    public const ?string PRIMARY_KEY = null;

    protected bool $created = false;

    /**
     * @var array<string, string>
     */
    protected static array $columns = [];

    protected static TableDefinition $tableDefinition;

    /**
     * @param array<string, mixed> $values
     */
    public function __construct(array $values)
    {
        $this->initColumns();

        foreach ($values as $key => $value) {
            $uKey = strtoupper($key);
            if (property_exists($this, $uKey)) {
                $this->{$uKey} = $value;
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function __toArray(): array
    {
        $array = [];
        $properties = call_user_func('get_object_vars', $this);
        foreach ($properties as $key => $value) {
            if (StringTools::isAllCaps($key)) {
                $array[$key] = $value;
            }
        }
        return $array;
    }

    public function __toString(): string
    {
        return '';
    }

    public function setCreated(bool $created): void
    {
        $this->created = $created;
    }

    public function wasCreated(): bool
    {
        return $this->created;
    }

    public static function getTableDefinition(): ?TableDefinition
    {
        if (empty(static::$columns) || empty(static::TABLE)) {
            return null;
        }

        if (! empty(static::$tableDefinition)) {
            return static::$tableDefinition;
        }

        return static::$tableDefinition = new TableDefinition(static::TABLE, static::$columns, static::PRIMARY_KEY);
    }

    public function getIdentity(): ?string
    {
        return $this->{static::IDENTITY_COLUMN};
    }

    /**
     * @return mixed[]
     */
    public static function getFields(): array
    {
        $fields = [];
        foreach (get_class_vars(static::class) as $key => $default) {
            if (StringTools::isAllCaps($key)) {
                $fields[] = $key;
            }
        }
        return $fields;
    }

    private function initColumns(): void
    {
        foreach ($this->columns as $key => $definition) {
            $uKey = strtoupper($key);
            $this->{$uKey} = null;
        }
    }
}
