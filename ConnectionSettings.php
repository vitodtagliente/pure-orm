<?php

namespace Pure\ORM;

// Classe di configurazione di una connessione database

class ConnectionSettings
{
    public const MySQL = 'mysql';
    public const SQLite = 'sqlite';

    // insieme dei campi della tabella
	private $properties = array();

	public function __get($key){
        if(array_key_exists($key, $this->properties))
		  return $this->properties[$key];
        return null;
	}

	public function __set( $key, $value){
		if(array_key_exists($key, $this->properties))
			$this->properties[$key] = $value;
	}

    public function __isset($key){
        return isset($this->properties[$key]);
    }

    public function __construct($data = array()){
        $this->properties = $data;
    }

    public function info(){
        return json_encode($this->properties);
    }

    public function get_type(){
    	if(isset($this->properties['type']))
    		return $this->properties['type'];
    	return self::MySQL;
    }

    public function mysql(){ $this->type = self::MySQL; return $this; }
    public function lite(){ $this->type = self::SQLite; return $this; }

    // ritorna la stringa di connessione per interfaccia pdo
    public function connection_string(){
        $connection_string = array();

        $type = $this->get_type();
        array_push($connection_string, $type);

        if($type == self::SQLite){
        	$filename = 'db.sqlite';
        	if(isset($this->properties['filename']))
        		$filename = $this->properties['filename'];
        	array_push($connection_string, ":$filename");
        }
        else {
	        array_push($connection_string, ':host=');
	        array_push($connection_string,
	            (isset($this->properties['host']))?
	            $this->properties['host']:'localhost'
	        );
	        array_push($connection_string, ';dbname=');
	        array_push($connection_string,
	            (isset($this->properties['name']))?
	            $this->properties['name']:null
	        );
        }
        
        array_push($connection_string, ';charset=utf8');
        return implode($connection_string);
    }
}

?>
