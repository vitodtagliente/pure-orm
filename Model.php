<?php

namespace Pure\ORM;

// Si può derivare lo schema dal modello?
// sarà mai possibile?

abstract class Model
{
	public function __construct(){
		$schema = self::schema();
        foreach($schema->properties() as $name => $descriptor){
            $this->properties[$name] = $descriptor->get_default();
            if($descriptor->is_primary())
            {
                array_push($this->identifiers, $name);
            }
        }
	}

	public function __destruct(){}

	// ritorna il nome della tabella
    // se non specificato
    // corrisponde al nome della classe del modello in minuscolo
    // resa al plurale
    // example: User -> users
	public static function table(){
		$path = explode('\\', get_called_class());
        return array_pop($path) . 's';
	}

	// insieme dei campi della tabella
	private $properties = array();
    // insieme dei campi che costituiscono la chiave
    private $identifiers = array();
	// specifica se il modello è di inserimento, perchè nuovo, 
    // o se è stato estratto da db 
    private $from_db = false;

	public function __get($key){
        if(array_key_exists($key, $this->properties))
		  return $this->properties[$key];
        return null;
	}

	public function __set( $key, $value){
		if(array_key_exists($key, $this->properties))
			$this->properties[$key] = $value;
	}

    /*
	// setta il campo con validazione
	public function set($key, $value)
    {
		if(array_key_exists($key, $this->properties))
		{
            $schema = self::schema();
            if(isset($schema))
            {
                $property = $schema->get($key);
                if(isset($property))
                {
                    $this->properties[$key] = $this->sanitize($value, $property->get_type());
                    return true;
                }                
            }
		}
		return false;
	}

    // si occupa di sanitizzare un valore in base al tipo
    private function sanitize($value, $type){
        dd("Sanitize: $value -> $type");
        return $value;
    }
    */

	// ritorna la lista di tutti i campi del modello
	public function columns() {
        return array_keys($this->properties);
    }

    // ritorna il modello in formato array associativo
    public function data(){
    	return $this->properties;
    }

    // ritorna la codifica json del modello
    public function json(){
    	return json_encode($this->properties);
    }

    // ritorna true se è un modello esistente
    public function exists(){
		if($this->from_db)
		{
            foreach ($this->identifiers as $id) {
                if( !isset($this->properties[$id]) )
                    return false;
            }
            return true;
       }
       return false;
    }

    private function getIdentifyCondition(){
        $value = '';
        $and = '';
        foreach ($this->identifiers as $id) {
            $value .= "$and $id = '".$this->properties[$id]."' ";
            $and = 'AND';
        }
        return $value;
    }

    // esegue l'insert se l'oggetto è nuovo
    // altrimenti la update
    public function save()
    {
    	if($this->exists())
    	{
			// update
            $result = false;
            $id = $this->getIdentifyCondition();
            if(isset($id))
            {
                $result = Database::main()->update(static::table(), $id, $this->data());
            }
            return $result;
    	}
    	else 
    	{
    		// insert
    		$result = Database::main()->insert(static::table(), $this->data());
            if($result){
                $this->from_db = true;
            }
            return $result;
    	}
    }

    // esegue la rimozione dell'elemento dal db
    public function erase(){
    	if($this->exists())
            return Database::main()->delete(static::table(), $this->getIdentifyCondition());
        return false;
    }

    public static function find($where){
        $classname = get_called_class();
        $model = new $classname();

        $result = Database::main()->select(static::table(), null, $where);
        if(!$result)
            return null;

        foreach( $result as $key => $value )
            $model->$key = $value;
        $model->from_db = true;
        return $model;
    }

    public static function all($where = null){
        $classname = get_called_class();
        $temp = new $classname();
        $models = array();

        $result = Database::main()->selectAll(static::table(), null, $where);
        if(!empty($result))
        {
            foreach ($result as $r)
            {
                $model = new $classname();
                foreach($r as $key => $value)
                    $model->$key = $value;
                $model->from_db = true;
                array_push($models, $model);
            }
        }
        return $models;
    }

    // Gestione degli schemi.
    // Per mantenere delle buone prestazioni
    // mi salvo l'istanza degli schemi generati
    // per tipologia di modello specificato. 
    private static $schemas = array();

    // produce lo schema del modello
    public static function schema(){
        $classname = get_called_class();
        if(!array_key_exists($classname, self::$schemas)){
            $schema = new SchemaBuilder(static::table());
            static::define($schema);
            self::$schemas[$classname] = $schema;
        }
        return self::$schemas[$classname];
    }

    // da derivare per la definizione dello schema    
    abstract public static function define($schema);

    // sovrascrivere questa funzione per definire i seed per modello
    // in fase di creazione dello schema
    public static function seed(){

    }
}