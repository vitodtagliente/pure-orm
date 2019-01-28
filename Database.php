<?php

namespace Pure\ORM;

// singleton di accesso alla connessione di database

class Database {
    // oggetto di connessione al database
    private $connection = null;
    // singleton pattern
    private static $instance;
    // configurazione di collegamento al database
    private static $connection_settings;

    function __construct(Connection $connection)
    {
        $this->connection = $connection;
        if($this->connection->isConnected() == false){
            if($this->connection->connect() == false)
            {
                exit("Database connection failed!");
            }
        }
    }

    public static function prepare(ConnectionSettings $settings){
    	self::$connection_settings = $settings;
    }

    public static function main(){
        if(!isset(self::$instance)){
            if(isset(self::$connection_settings))
            {
                self::$instance = new Database(new Connection(self::$connection_settings, false));
                    self::$connection_settings = null;
            }
        	else exit("Database was not prepared with a valid ConnectionSettings");
        }
        return self::$instance;
    }

    public static function bind($connection){
        if(isset(self::$instance))
            self::$instance->connection = $connection;
    }

    public static function end(){
    	if(isset(self::$instance))
    		self::$instance->close();
    }

    public function isConnected(){
        if(isset($this->connection))
            return $this->connection->isConnected();
        return false;
    }

    public function getPdo(){
        return $this->connection->getPdo();
    }

    function close(){
        if($this->isConnected())
            $this->connection->disconnect();
    }

    function __destruct(){
        $this->close();
    }
}

?>
