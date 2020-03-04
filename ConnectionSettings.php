<?php

/// Copyright (c) Vito Domenico Tagliente
///
/// Database connection settings
/// This class lets to configure the database connection

namespace Pure\ORM;

class ConnectionSettings
{
    /// Connection Types
    public const TYPE_MySQL = 'mysql';
    public const TYPE_SQLite = 'sqlite';

    /// settings properties
    private array $m_properties;

    /// constructor
    /// @param data - The initial data
    public function __construct(?array $data)
    {
        // check if it is an associative array
        if (array_keys($data) !== range(0, count($data) - 1))
        {
            $this->m_properties = $data;
        }
    }

    /// destructor
    public function __destruct()
    {

    }

    /// Retrieve a property
    /// @param name - The name of the property
    /// @return - The property value
    public function __get(string $name)
    {
        if (array_key_exists($name, $this->m_properties))
            return $this->m_properties[$name];
        return null;
    }

    /// Set a property
    /// @param name - The name of the property
    /// @param value - The value
    public function __set(string $name, $value) : void
    {
        if (array_key_exists($name, $this->m_properties))
            $this->m_properties[$name] = $value;
    }

    /// isset implementation
    /// Check if a property is defined
    /// @param name - The name of the property
    /// @return - True if exists
    public function __isset(string $name) : bool
    {
        return isset($this->m_properties[$name]);
    }

    /// Retrieve the json representation
    /// @return - The json format
    public function toJson() : string
    {
        return json_encode($this->m_properties);
    }

    /// Retrieve the current charset
    /// @return - The charset
    private function getCharset() : string
    {
        if (isset($this->charset))
            return $this->charset;
        return 'utf8';
    }

    /// Retrieve the connection type
    /// @return - The connection type
    public function getType() : string
    {
        if (isset($this->type)) return $this->type;
        return self::TYPE_MySQL;
    }

    /// Set the connection type
    /// @param type - The connection type
    public function setType(string $type) : void
    {
        $this->type = $type;
    }

    /// Retrieve a PDO format connection string
    /// @return - The connection string
    public function toString() : string
    {
        $connection_string = array();

        $type = $this->getType();
        array_push($connection_string, $type);

        if ($type == self::TYPE_SQLite)
        {
            array_push($connection_string,
                ':' . (isset($this->filename)) ? $this->filename : 'db.sqlite');
        }
        else
        {
            array_push($connection_string, ':host=');
            array_push($connection_string,
                (isset($this->host)) ? $this->host : 'localhost'
            );
            array_push($connection_string, ';dbname=');
            array_push($connection_string,
                (isset($this->name)) ? $this->name : null
            );
        }

        array_push($connection_string, ';charset=');
        array_push($connection_string, $this->getCharset());

        return implode($connection_string);
    }
}
