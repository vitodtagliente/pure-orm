<?php

/// Copyright (c) Vito Domenico Tagliente
/// Database connection implementation

namespace Pure\ORM;

class Connection
{
    private $m_context = null;
    private $m_error = null;
    private $m_settings = null;
    private $m_connectionException = null;

    /// constructor
    /// @param settings - The connection settings
    /// @param auto_connect - Specify to auto connect if true
    public function __construct(ConnectionSettings $settings, $auto_connect = true)
    {
        $this->m_settings = $settings;
        if ($auto_connect) {
            $this->connect();
        }
    }

    /// destructor
    public function __destruct()
    {
        if ($this->isConnected()) {
            $this->disconnect();
        }
    }

    /// Perform a connection
    /// @return - True if succeed
    public function connect()
    {
        try {
            $this->m_context = new \PDO(
                $this->m_settings->toConnectionString(),
                $this->m_settings->username,
                $this->m_settings->password,
                $this->m_settings->options
            );
            $this->m_context->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->m_context->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            return true;
        } catch (\PDOException $e) {
            $this->m_context = null;
            $this->m_error = $e->getMessage();
            $this->m_connectionException = $e;
            return false;
        }
    }

    /// Perform a disconnection
    public function disconnect()
    {
        $this->m_context = null;
        $this->m_error = null;
        $this->m_connectionException = null;
    }

    /// Check the status of the connection
    /// @return - True if connected
    public function isConnected()
    {
        return isset($this->m_context);
    }

    /// Retrieve the error message if any
    /// @return - The error message
    public function getErrorMessage()
    {
        return $this->m_error;
    }

    ///Retrieve the connection exception if any
    /// @return - The connection exception
    public function getException()
    {
        return $this->m_connectionException;
    }

    /// Retrieve the PDO object
    /// @return - The PDO context
    public function getPDO()
    {
        return $this->m_context;
    }

    /// Retrieve the connection settings
    /// @return - The Connection Settings
    public function getSettings()
    {
        return $this->m_settings;
    }
}
