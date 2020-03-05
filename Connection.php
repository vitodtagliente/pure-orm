<?php

/// Copyright (c) Vito Domenico Tagliente
///
/// Database connection implementation

namespace Pure\ORM;

class Connection
{
    /// The PDO
    private ?\PDO $m_PDO = null;
    /// connection settings
    private ?ConnectionSettings $m_settings = null;
    /// cache any connection error
    private string $m_error;
    /// store the exception
    private string $m_connectionException;

    /// constructor
    /// @param settings - The connection settings
    /// @param auto_connect - Specify to auto connect if true
    public function __construct(ConnectionSettings $settings, bool $connect = true)
    {
        $this->m_settings = $settings;
        if ($connect)
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
            $this->m_PDO = new \PDO(
                $this->m_settings->toString(),
                $this->m_settings->username,
                $this->m_settings->password,
                $this->m_settings->options
            );

            $this->m_PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->m_PDO->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

            $this->m_error = '';
            $this->m_connectionException = '';
            return true;
        }
        catch (\PDOException $e)
        {
            $this->m_PDO = null;
            $this->m_error = $e->getMessage();
            $this->m_connectionException = $e;
            return false;
        }
    }

    /// Perform a disconnection
    public function disconnect(): void
    {
        $this->m_PDO = null;
        $this->m_error = '';
        $this->m_connectionException = '';
    }

    /// Check the status of the connection
    /// @return - True if connected
    public function isConnected(): bool
    {
        return isset($this->m_PDO);
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
    public function getPDO(): ?\PDO
    {
        return $this->m_PDO;
    }

    /// Retrieve the connection settings
    /// @return - The Connection Settings
    public function getSettings(): ?ConnectionSettings
    {
        return $this->m_settings;
    }
}
