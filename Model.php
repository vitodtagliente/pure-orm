<?php

namespace Pure\ORM;

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

    public function __isset($key){
        return isset($this->properties[$key]);
    }

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
        $value = array();
        foreach ($this->identifiers as $id) {
            array_push($value, "$id = '".$this->properties[$id]."'");
        }
        return implode(' AND ', $value);
    }

    // esegue l'insert se l'oggetto è nuovo
    // altrimenti la update
    public function save()
    {
    	if($this->exists())
    	{
			// update
			$query = new Query(static::table());
			$query->update($this->data(), $this->getIdentifyCondition());
			return $query->execute();
    	}
    	else
    	{
    		// insert
			$query = new Query(static::table());
			$query->insert($this->data());
            if($query->execute()){
                $this->from_db = true;
            }
            return $query->success();
    	}
    }

    // esegue la rimozione dell'elemento dal db
    public function erase(){
    	if($this->exists())
		{
			$query = new Query(static::table());
			$query->delete()->where($this->getIdentifyCondition());
			return $query->execute();
		}
        return false;
    }

	private function sanitize(){
		$schema = self::schema();
        foreach($schema->properties() as $name => $descriptor){
            if($descriptor->get_type() == 'BOOL')
			{
				if(isset($this->properties[$name]))
					$this->properties[$name] = ($this->properties[$name] == 1)?true:false;
			}
        }
	}

    public static function find($where){
        $classname = get_called_class();
        $model = new $classname();

        if(is_numeric($where))
        {
            $where = "id = '$where'";
        }

		$query = new Query(static::table());
		$query->select()->where($where);
		$pr = $query->execute();
        if(!$query->success())
            return null;

		$model->properties = $pr;
		$model->from_db = true;
		$model->sanitize();
        return $model;
    }

    public static function insert($models){
        if(is_array($models)){
            $records = array();
            foreach ($models as $model) {
                array_push($records, $model->data());
            }
			$query = new Query(static::table());
			$query->insert($records);
			return $query->execute();
        }
        else
            return $models->save();
    }

    public static function all($where = null, $statement = null){
        $classname = get_called_class();
        $temp = new $classname();
        $models = array();

		$query = new Query(static::table());
		$query->select()->all()->where($where)->statement($statement);
		$result = $query->execute();
        if(!empty($result))
        {
            foreach ($result as $record)
            {
                $model = new $classname();
				$model->properties = $record;
                $model->from_db = true;
				$model->sanitize();
                array_push($models, $model);
            }
        }
        return $models;
    }

    public static function count(){
        $query = new Query(static::table());
        $query->count();
        $result = $query->execute();
        if(is_integer($result))
            return $result;
        return -1;
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

?>
