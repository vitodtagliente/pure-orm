<?php

namespace Pure\ORM;

// Questa classe modella la connessione a database
// vuole in input un ConnectionSettings

class Connection
{
    private $context = null;
    private $error = null;
    private $settings = null;
    private $connection_exception = null;

    public function __construct(ConnectionSettings $settings, $auto_connect = true)
    {
        $this->settings = $settings;
        if($auto_connect)
            $this->connect();
    }

    public function __destruct(){
        if($this->isConnected())
            $this->disconnect();
    }

    public function connect()
    {
        try
        {
            $this->context = new \PDO(
                $this->settings->getConnectionString(),
                $this->settings->username,
                $this->settings->password,
                $this->settings->options
            );
            $this->context->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->context->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            return true;
        }
        catch(\PDOException $e)
        {
            $this->context = null;
            $this->error = $e->getMessage();
            $this->connection_exception = $e;
            return false;
        }
    }

    public function disconnect(){
        $this->context = null;
        $this->error = null;
        $this->connection_exception = null;
    }

    public function isConnected(){
        return isset($this->context);
    }

    public function getErrorMessage(){
        return $this->error;
    }

    public function getException(){
        return $this->$connection_exception;
    }

    public function getPdo(){
        return $this->context;
    }

    // ritorna le informazioni di connessione
    public function getSettings(){
        return $this->settings;
    }
}

?>
