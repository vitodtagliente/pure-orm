<?php

namespace Pure\ORM;

// Classe di configurazione di una connessione database
// prende in input un array di configurazione (opzionale)
// e permette di configurare manualmente
// la confiugurazione della connessione

class ConnectionSettings
{
    public const MySQL = 'mysql';
    public const SQLite = 'sqlite';

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

    public function __construct(array $data = array()){
        $this->properties = $data;
    }

    public function toJson(){
        return json_encode($this->properties);
    }

    private function getCharset(){
        if(isset($this->charset))
            return $this->charset;
        return 'utf8';
     }

    public function getType(){
    	if(isset($this->type)) return $this->type;
    	return self::MySQL;
    }

    // configura il tipo di database
    public function mysql(){ $this->type = self::MySQL; return $this; }
    public function lite(){ $this->type = self::SQLite; return $this; }

    // ritorna la stringa di connessione per interfaccia pdo
    public function getConnectionString(){
        $connection_string = array();

        $type = $this->getType();
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
        
        array_push($connection_string, ';charset=');
        array_push($connection_string, $this->getCharset());
        return implode($connection_string);
    }
}

?>
