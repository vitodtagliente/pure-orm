<?php

namespace Pure\ORM;

class Database {
    // oggetto di connessione al database
    private $connection = null;
    // singleton pattern
    private static $instance;
    // configurazione di collegamento al database
    private static $connection_settings;

    function __construct($settings)
    {
        $this->connection = new Connection($settings);
        if($this->connection->is_connected() == false)
        {
            // security issue
            // echo ($this->connection->getInfo());
            exit("Database connection failed!");
        }
    }

    public static function prepare($settings){
    	self::$connection_settings = $settings;
    }

    public static function main(){
        if(!isset(self::$instance)){
            if(isset(self::$connection_settings))
            {
                self::$instance = new Database(self::$connection_settings);
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

    public function error_reporting($active = true){
        $this->debug = $active;
    }

    public function is_connected(){
        if(isset($this->connection))
            return $this->connection->is_connected();
        return false;
    }

    public function pdo(){
        return $this->connection->get_context();
    }

    function close(){
        if($this->is_connected())
            $this->connection->disconnect();
    }

    function __destruct(){
        $this->close();
    }
}

?>
