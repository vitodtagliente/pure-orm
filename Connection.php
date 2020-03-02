<?php

/// Copyright (c) Vito Domenico Tagliente
/// Database connection implementation

namespace Pure\ORM;

class Connection
{
    /// represents the PDO
    private $m_context = null;
    /// cache any connection error
    private $m_error = null;
    /// connection settings
    private $m_settings = null;
    /// store the exception
    private $m_connectionException = null;

    /// constructor
    /// @param settings - The connection settings
    /// @param auto_connect - Specify to auto connect if true
    public function __construct(ConnectionSettings $settings, $auto_connect = true)
    {
        $this->m_settings = $settings;
        if ($auto_connect)
        {
            $this->connect();
        }
    }

    /// destructor
    public function __destruct()
    {
        if ($this->isConnected())
        {
            $this->disconnect();
        }
    }

    /// Perform a connection
    /// @return - True if succeed
    public function connect(): bool
    {
        try
        {
            $this->m_context = new \PDO(
                $this->m_settings->toConnectionString(),
                $this->m_settings->username,
                $this->m_settings->password,
                $this->m_settings->options
            );
            $this->m_context->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->m_context->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            return true;
        }
        catch (\PDOException $e)
        {
            $this->m_context = null;
            $this->m_error = $e->getMessage();
            $this->m_connectionException = $e;
            return false;
        }
    }

    /// Perform a disconnection
    public function disconnect(): void
    {
        $this->m_context = null;
        $this->m_error = null;
        $this->m_connectionException = null;
    }

    /// Check the status of the connection
    /// @return - True if connected
    public function isConnected(): bool
    {
        return isset($this->m_context);
    }

    /// Retrieve the error message if any
    /// @return - The error message
    public function getErrorMessage(): string
    {
        return $this->m_error;
    }

    ///Retrieve the connection exception if any
    /// @return - The connection exception
    public function getException(): string
    {
        return $this->m_connectionException;
    }

    /// Retrieve the PDO object
    /// @return - The PDO context
    public function getPDO(): \PDO
    {
        return $this->m_context;
    }

    /// Retrieve the connection settings
    /// @return - The Connection Settings
    public function getSettings(): ConnectionSettings
    {
        return $this->m_settings;
    }
}
