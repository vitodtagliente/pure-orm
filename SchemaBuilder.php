<?php

/// Copyright (c) Vito Domenico Tagliente
///
/// This class lets to define database table using the code first approach

namespace Pure\ORM;

class SchemaBuilder
{
    /// The database table name
    private string $m_table;
    /// The properties
    private array $m_properties = array();
    /// If the primary key is defined by multiple fields
    private array $m_compositeKeys = array();

    /// constructor
    /// @param tablename - The table name
    public function __construct(string $tablename)
    {
        $this->m_table = $tablename;
    }

    /// destructor
    public function __destruct()
    {
    }

    /// Retrieve a schema property
    /// @param name - The name of the property
    /// @return - The property
    public function get(string $name) : SchemaPropertyDescriptor
    {
        if (array_key_exists($name, $this->m_properties))
        {
            return $this-m_>properties[$name];
        }
        return null;
    }

    /// Add a property to the schema
    /// @param name - The name of the property
    /// @param type - The type of the property
    /// @return - The property
    public function add(string $name, string $type) : SchemaPropertyDescriptor
    {
        if (!array_key_exists($name, $this->m_properties))
        {
            $this->m_properties[$name] = new SchemaPropertyDescriptor($name, $type);
        }
        return $this->m_properties[$name];
    }

    /// Add the id field to the schema
    /// @param name - The name of the field
    /// @return - The Schema Builder
    public function id(string $name = 'id') : SchemaPropertyDescriptor
    {
        return $this->integer($name)->primary()->increments();
    }

    /// Add a boolean field to the schema
    /// @param name - The name of the field
    /// @return - The Schema Builder
    public function boolean(string $name) : SchemaPropertyDescriptor
    {
        return $this->add($name, SchemaPropertyDescriptor::TYPE_BOOL);
    }

    /// Add an integer field to the schema
    /// @param name - The name of the field
    /// @return - The Schema Builder
    public function integer(string $name) : SchemaPropertyDescriptor
    {
        return $this->add($name, SchemaPropertyDescriptor::TYPE_INT);
    }

    /// Add a float field to the schema
    /// @param name - The name of the field
    /// @return - The Schema Builder
    public function float(string $name) : SchemaPropertyDescriptor
    {
        return $this->add($name, SchemaPropertyDescriptor::TYPE_FLOAT);
    }

    /// Add a char field to the schema
    /// @param name - The name of the field
    /// @param size - The size of the char field
    /// @return - The Schema Builder
    public function char(string $name, int $size = 30) : SchemaPropertyDescriptor
    {
        return $this->add($name, SchemaPropertyDescriptor::TYPE_VARCHAR . "($size)");
    }

    /// Add a text field to the schema
    /// @param name - The name of the field
    /// @return - The Schema Builder
    public function text(string $name) : SchemaPropertyDescriptor
    {
        return $this->add($name, SchemaPropertyDescriptor::TYPE_TEXT);
    }

    /// Add a date field to the schema
    /// @param name - The name of the field
    /// @return - The Schema Builder
    public function date(string $name) : SchemaPropertyDescriptor
    {
        return $this->add($name, SchemaPropertyDescriptor::TYPE_DATE);
    }

    /// Add a time field to the schema
    /// @param name - The name of the field
    /// @return - The Schema Builder
    public function time(string $name) : SchemaPropertyDescriptor
    {
        return $this->add($name, SchemaPropertyDescriptor::TYPE_TIME);
    }

    /// Add a datetime field to the schema
    /// @param name - The name of the field
    /// @return - The Schema Builder
    public function datetime(string $name) : SchemaPropertyDescriptor
    {
        return $this->add($name, SchemaPropertyDescriptor::TYPE_DATETIME);
    }

    /// Add a timestamps field to the schema
    /// @return - The Schema Builder
    public function timestamps() : void
    {
        $this->datetime('created_at');
        $this->datetime('updated_at');
    }

    // make a composite primary key
    public function primary($names = array())
    {
        // TODO: chiave primaria composta
    }

    /// Retrieve the name of all properties
    /// @return - The array of the property names
    public function getNames() : array
    {
        return array_keys($this->m_properties);
    }

    /// Retrieve the properties of the schema
    /// @return - The array of properties
    public function getProperties() : array
    {
        return $this->m_properties;
    }

    /// Generate the query able to create the schema
    /// @return - The query
    public function toQuery() : string
    {
        $query = array();
        array_push($query, "CREATE TABLE " . $this->m_table . " (");
        $comma = '';
        foreach ($this->m_properties as $name => $descriptor)
        {
            array_push($query, "$comma\n\t" . $descriptor->toQuery($this->m_table));
            $comma = ',';
        }
        array_push($query, "\n)");
        return implode($query);
    }
}