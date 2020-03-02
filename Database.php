<?php

/// Copyright (c) Vito Domenico Tagliente
/// Database implementation

namespace Pure\ORM;

class Database
{
    /// singleton pattern
    private static $s_instance;
    /// cached connection settings
    /// used to perform the connection only if needed
    private static $s_connectionSettings;
    /// connection context
    private $m_connection = null;

    /// constructor
    /// @param connection - The connection
    public function __construct(Connection $connection)
    {
        $this->m_connection = $connection;
        if (!$this->m_connection->isConnected())
        {
            if ($this->m_connection->connect() == false)
            {
                throw new \Exception("Database connection failed!");
            }
        }
    }

    /// destructor
    public function __destruct()
    {
        $this->close();
    }

    /// Prepare the connection configuration
    /// @param settings - The connection settings
    public static function prepare(ConnectionSettings $settings) : void
    {
        self::$s_connectionSettings = $settings;
    }

    /// singleton pattern
    /// @return - The database instance
    public static function main() : Database
    {
        if (!isset(self::$s_instance))
        {
            if (isset(self::$s_connectionSettings))
            {
                self::$s_instance = new Database(new Connection(self::$s_connectionSettings, false));
                self::$s_connectionSettings = null;
            }
            else
            {
                throw new \Exception("Database was not prepared with a valid ConnectionSettings");
            }
        }
        return self::$s_instance;
    }

    /// Used to bind a connection
    /// @param connection - The connection
    public static function bind($connection) : void
    {
        if (isset(self::$s_instance))
            self::$instance->s_connection = $connection;
    }

    public static function end() : void
    {
        if (isset(self::$instance))
            self::$instance->close();
    }

    /// Check if the database is connected
    /// @return - True if connected
    public function isConnected() : bool
    {
        if (isset($this->m_connection))
            return $this->m_connection->isConnected();
        return false;
    }

    /// Retrieve the PDO
    /// @return - The PDO object
    public function getPDO() : \PDO
    {
        return $this->m_connection->getPDO();
    }

    /// Close the current connection
    function close() : void
    {
        if ($this->isConnected())
            $this->m_connection->disconnect();
    }
}