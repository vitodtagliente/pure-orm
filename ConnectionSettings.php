<?php

namespace Pure\ORM;

// Classe utilizzata per rappresentare
// le informazioni di connessione ad un database

class ConnectionSettings
{
    public const MySQL = 'mysql';

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

    public function connection_string(){
        $connection_string = array();
        array_push($connection_string,
            (isset($this->properties['type']))?
            $this->properties['type']:self::MySQL
        );
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
        array_push($connection_string, ';charset=utf8');
        return implode($connection_string);
    }
}

?>
