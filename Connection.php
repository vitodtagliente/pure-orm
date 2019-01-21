<?php

namespace Pure\ORM;

// Questa classe si occupa di gestire la connessione al Database

class Connection
{
    private $context = null;
    private $error = null;
    private $settings = null;
    private $connection_exception = null;

    public function __construct($settings, $auto_connect = true)
    {
        if(is_array($settings))
            $this->settings = new ConnectionSettings($settings);

        if(!is_a($this->settings, '\Pure\ORM\ConnectionSettings')){
            var_dump($this->settings);
            exit("\nInvalid ConnectionSettings");
        }

        if($auto_connect)
            $this->connect();
    }

    public function __destruct(){
        if($this->is_connected())
            $this->disconnect();
    }

    public function connect()
    {
        try
        {
            $this->context = new \PDO(
                $this->settings->connection_string(),
                $this->settings->username,
                $this->settings->password,
                $this->settings->options
            );
            $this->context->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
            $this->context->setAttribute( \PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC );
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

    public function is_connected(){
        return isset($this->context);
    }

    public function error_message(){
        return $this->error;
    }

    public function exception(){
        return $this->$connection_exception;
    }

    public function get_context(){
        return $this->context;
    }

    public function info(){
        return $this->settings->info();
    }
}

?>
