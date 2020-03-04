<?php

/// Copyright (c) Vito Domenico Tagliente
///
/// Schema property implementation

namespace Pure\ORM;

class SchemaPropertyDescriptor
{
    public const TYPE_BOOL = 'BOOL';
    public const TYPE_DATE = 'DATE';
    public const TYPE_DATETIME = 'DATETIME';
    public const TYPE_FLOAT = 'FLOAT';
    public const TYPE_INT = 'INT';
    public const TYPE_TEXT = 'TEXT';
    public const TYPE_TIME = 'TIME';
    public const TYPE_VARCHAR = 'VARCHAR';

    /// The name of the property
    private string $m_name;
    /// The type of the property
    private string $m_type;
    /// The default value
    private $m_defaultValue = null;
    /// Is it an auto increment field?
    private bool $m_isAutoIncrements = false;
    /// Is it nullable?
    private bool $m_isNullable = false;
    /// Is it a primary key?
    private bool $m_isPrimary = false;
    /// Is it unique?
    private bool $m_isUnique = false;
    /// Is is unique?
    private bool $m_isUnsigned = false;
    /// Is is a foreign key?
    private bool $m_isForeign = false;
    /// The foreign key class
    private string $m_foreignClass;
    /// The foreign property
    private string $m_foreignProperty;

    /// constructor
    public function __construct(string $name, string $type)
    {
        $this->m_name = $name;
        $this->m_type = $type;
    }

    /// destructor
    public function __destruct()
    {
    }

    /// Set the default value
    /// @param value - The default value
    /// @return - The property descriptor
    public function default($value) : SchemaPropertyDescriptor
    {
        $this->m_defaultValue = $value;
        return $this;
    }

    /// Set it as auto increments
    /// @return - The property descriptor
    public function increments() : SchemaPropertyDescriptor
    {
        $this->m_isAutoIncrements = true;
        return $this;
    }

    /// Set as a nullable property
    /// @return - The property descriptor
    public function nullable() : SchemaPropertyDescriptor
    {
        $this->m_isNullable = true;
        return $this;
    }

    /// Set as a primary key property
    /// @return - The property descriptor
    public function primary() : SchemaPropertyDescriptor
    {
        $this->m_isPrimary = true;
        return $this;
    }

    /// Set as a unique property
    /// @return - The property descriptor
    public function unique() : SchemaPropertyDescriptor
    {
        $this->m_isUnique = true;
        return $this;
    }

    /// Set as an unsigned property
    /// @return - The property descriptor
    public function unsigned() : SchemaPropertyDescriptor
    {
        $this->m_isUnsigned = true;
        return $this;
    }

    /// Set as a foreign key
    /// @param modelClass - The model class to link
    /// @param propertyName - The property to link
    /// @return - The property descriptor
    public function link(string $modelClass, string $propertyName = 'id') : SchemaPropertyDescriptor
    {
        if (class_exists($modelClass)
            && is_subclass_of($modelClass, '\Pure\ORM\Model')
            && isset($propertyName))
        {
            $this->m_isForeign = true;
            $this->m_foreignClass = $modelClass::table();
            $this->m_foreignProperty = $propertyName;
        }
        return $this;
    }

    /// Get the default value
    /// @return - The default value
    public function getDefaultValue()
    {
        return $this->m_defaultValue;
    }

    /// Check if it is a primary key
    /// @return  - True if it is
    public function isPrimaryKey() : bool
    {
        return $this->m_isPrimary;
    }

    /// Retrieve the type
    /// @return - The type
    public function getType() : string
    {
        return $this->m_type;
    }

    /// Generate the query statement
    /// @param table - The database table
    /// @return - The query statement
    public function toQuery(string $table) : string
    {
        // field declaration
        $query = array();
        array_push($query, $this->m_name . ' ' . $this->m_type);
        array_push($query, ($this->m_isNullable) ? ' NULL' : ' NOT NULL');

        if (isset($this->m_defaultValue))
        {
            $value = $this->m_defaultValue;
            if ($this->m_type == self::TYPE_BOOL)
            {
                $value = ($this->m_defaultValue) ? 1 : 0;
            }
            array_push($query, " DEFAULT '$value'");
        }

        if ($this->m_isAutoIncrements && $this->m_type == self::TYPE_INT)
        {
            array_push($query, ' AUTO_INCREMENT');
        }

        // constraints
        if ($this->m_isPrimary)
        {
            array_push(
                $query,
                ",\n\t" . 'CONSTRAINT PK_' . $this->m_name . ' PRIMARY KEY (' . $this->m_name . ')'
            );
        }

        if ($this->m_isUnique)
        {
            array_push(
                $query,
                ",\n\t" . 'CONSTRAINT UC_' . $this->m_name . ' UNIQUE KEY (' . $this->m_name . ')'
            );
        }

        if ($this->m_isForeign)
        {
            array_push(
                $query,
                ",\n\tCONSTRAINT FK_$table" . $this->m_foreignClass . '_' . $this->m_name .
                ' FOREIGN KEY (' . $this->m_name . ") REFERENCES " . $this->m_foreignClass .
                " (" . $this->m_foreignProperty . ')');
        }

        return implode($query);
    }
}