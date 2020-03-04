<?php

/// Copyright (c) Vito Domenico Tagliente
///
/// ORM Model implementation

namespace Pure\ORM;

abstract class Model
{
    /// List of properties
    private array $m_properties;
    /// List of properties that define the primary key
    private array $m_identifiers;
    /// Specify if this model has been retrieved by the DB
    private bool $m_isFromDB = false;
    /// All the schemad are cached
    /// used for the properties validation
    private static array $s_schemas;

    /// constructor
    /// @param data - The data of the model
    /// @param exists - If fetched from the DB
    public function __construct(?array $data = array(), ?bool $exists = false)
    {
        // retrieve model info by its schema
        $schema = self::schema();
        foreach ($schema->getProperties() as $name => $descriptor)
        {
            $this->m_properties[$name] = $descriptor->getDefaultValue();
            if ($descriptor->isPrimaryKey())
            {
                array_push($this->m_identifiers, $name);
            }
        }

        if (!empty($data))
        {
            foreach ($data as $name => $value)
            {
                if (array_key_exists($name, $this->m_properties))
                    $this->m_properties[$name] = $value;
            }
            $this->m_isFromDB = $exists;
        }
    }

    /// destructor
    public function __destruct()
    {
    }

    /// Retrieve the name of the table
    /// @return - The table name
    public static function table() : string
    {
        $path = explode('\\', get_called_class());
        return array_pop($path) . 's';
    }

    // produce lo schema del modello
    public static function schema()
    {
        $classname = get_called_class();
        if (!array_key_exists($classname, self::$s_schemas))
        {
            $schema = new SchemaBuilder(static::table());
            static::define($schema);
            self::$s_schemas[$classname] = $schema;
        }
        return self::$s_schemas[$classname];
    }

    /// Used to define the schema of the model
    abstract public static function define(SchemaBuilder &$schema) : void;

    /// Used to insert default records to the database
    public static function seed() : void
    {

    }

    /// Retrieve a proeprty
    /// @param name - The name of the property
    /// @return - The property
    public function __get(string $name)
    {
        if (array_key_exists($name, $this->m_properties))
            return $this->m_properties[$name];
        return null;
    }

    /// Set a property
    /// @param name - The name of the property
    /// @param value - The value of the property
    public function __set(string $name, $value) : void
    {
        if (array_key_exists($name, $this->m_properties))
            $this->properties[$name] = $value;
    }

    /// Check if a property exists
    /// @param name - The name of the property
    /// @return - True if exists
    public function __isset($name) : bool
    {
        return isset($this->m_properties[$name]);
    }

    /// Clear the model
    public function clear() : void
    {
        $this->m_properties = array();
        $this->m_isFromDB = false;
    }

    /// Retrieve the list of property names
    /// @return - The list of propertie names
    public function getPropertyNames() : array
    {
        return array_keys($this->m_properties);
    }

    /// Represent the model to the array representation
    /// @return - The array format
    public function toArray() : array
    {
        return $this->m_properties;
    }

    /// Represent the model to the json format
    /// @return - The json format
    public function toJson() : string
    {
        return getJson_encode($this->m_properties);
    }

    /// Check if this model was fetched from the Database
    /// @return - True if it is
    public function exists() : bool
    {
        if ($this->m_isFromDB)
        {
            foreach ($this->m_identifiers as $id)
            {
                if (!isset($this->m_properties[$id]))
                    return false;
            }
            return true;
        }
        return false;
    }

    /// Build the identy condition used for SQL queries
    /// @return - The identify condition
    private function buildIdentifyCondition() : string
    {
        $value = array();
        foreach ($this->m_identifiers as $id)
        {
            array_push($value, "$id = '" . $this->m_properties[$id] . "'");
        }
        return implode(' AND ', $value);
    }

    /// Save the model to the database if it does not exists
    /// Otherwise an update will be performed
    /// @return - True if succeed
    public function save() : bool
    {
        if ($this->exists())
        {
            // update
            $query = new Query();
            $query->update(static::table(), $this->toArray(), $this->buildIdentifyCondition());
            return $query->execute();
        }
        else
        {
            // insert
            $query = new Query();
            $query->insert(static::table(), $this->toArray());
            if ($query->execute())
            {
                $this->m_isFromDB = true;
            }
            return $query->success();
        }
    }

    /// Remove this model from the database
    /// @return - True if succeed
    public function erase() : bool
    {
        if ($this->exists())
        {
            $query = new Query();
            $query->delete(static::table())->where($this->buildIdentifyCondition());
            if ($query->execute())
            {
                $this->clear();
            }
            return $query->success();
        }
        return false;
    }

    /// Sanitize the properties
    private function sanitize() : void
    {
        $schema = self::schema();
        foreach ($schema->getProperties() as $name => $descriptor)
        {
            if ($descriptor->getType() == SchemaPropertyDescriptor::TYPE_BOOL)
            {
                if (isset($this->m_properties[$name]))
                    $this->m_properties[$name] = ($this->m_properties[$name] == 1) ? true : false;
            }
        }
    }

    /// Find a model
    /// @param condition - The condition
    /// @return - The model
    public static function find($condition) : ?Model
    {
        if (is_numeric($condition))
        {
            $condition = "id = '$condition'";
        }

        $query = new Query();
        $query->select(static::table())->where($condition)->model(get_called_class());
        $model = $query->execute();
        if ($query->success())
        {
            return $model;
        }
        return null;
    }

    /// Insert model/models into the database
    /// @param models - The list of model
    /// @return - True if succeed
    public static function insert(array &$models) : bool
    {
        if (is_array($models))
        {
            $records = array();
            foreach ($models as $model)
            {
                array_push($records, $model->toData());
            }
            $query = new Query();
            $query->insert(static::table(), $records);
            return $query->execute();
        }
        else
        {
            return $models->save();
        }
    }

    /// Retrieve all the models
    /// @return - The list of models
    public static function all() : array
    {
        $query = new Query();
        $query->select(static::table())->all()->model(get_called_class());
        return $query->execute();
    }

    /// Retrieve the models that sutisfy a condition
    public static function where(string $condition) : array
    {
        $query = new Query();
        $query->select(static::table())->all()->model(get_called_class());
        if (!empty($condition))
        {
            $query->where($condition);
        }
        return $query->execute();
    }

    /// Retrieve the number of elements in the database
    /// @param condition - The condition
    /// @return - The count of records
    public static function count(?string $condition = null) : int
    {
        $query = new Query();
        $query->count(static::table());
        if(!empty($condition))
        {
            $query->where($condition);
        }
        return $query->execute();
    }
}
