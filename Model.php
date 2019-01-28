<?php

namespace Pure\ORM;

abstract class Model
{
	public function __construct(array $data = array(), bool $exists = false){
		$schema = self::schema();
        foreach($schema->getProperties() as $name => $descriptor){
            $this->properties[$name] = $descriptor->getDefaultValue();
            if($descriptor->isPrimaryKey()){
                array_push($this->identifiers, $name);
            }
        }

        if(!empty($data)){
            foreach ($data as $name => $value) {
                if(array_key_exists($name, $this->properties))
                    $this->properties[$name] = $value;
            }
            $this->from_db = $exists;
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

    // pulisce il modello
    public function clear(){
        $this->properties = array();
        $this->from_db = false;
    }

	// ritorna la lista di tutti i campi del modello
	public function getColumns() {
        return array_keys($this->properties);
    }

    // ritorna il modello in formato array associativo
    public function getData(){
    	return $this->properties;
    }

    // ritorna la codifica getJson del modello
    public function getJson(){
    	return getJson_encode($this->properties);
    }

    // ritorna true se è un modello esistente
    public function exists(){
		if($this->from_db)
		{
            foreach ($this->identifiers as $id) {
                if(!isset($this->properties[$id]))
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
			$query->update($this->getData(), $this->getIdentifyCondition());
			return $query->execute();
    	}
    	else
    	{
    		// insert
			$query = new Query(static::table());
			$query->insert($this->getData());
            if($query->execute())
                $this->from_db = true;
            return $query->success();
    	}
    }

    // esegue la rimozione dell'elemento dal db
    public function erase(){
    	if($this->exists())
		{
			$query = new Query(static::table());
			$query->delete()->where($this->getIdentifyCondition());
			if($query->execute())
                $this->clear();
            return $query->success();
		}
        return false;
    }

	private function sanitize(){
		$schema = self::schema();
        foreach($schema->getProperties() as $name => $descriptor){
            if($descriptor->getType() == 'BOOL')
			{
				if(isset($this->properties[$name]))
					$this->properties[$name] = ($this->properties[$name] == 1)?true:false;
			}
        }
	}

    public static function find(int $id){
		$query = new Query(static::table());
		$query->select()->where("id = '$id'")->model(get_called_class());
		$model = $query->execute();
        if(!$query->success())
            return null;
        return $model;
    }

    public static function insert($models){
        if(is_array($models)){
            $records = array();
            foreach ($models as $model) {
                array_push($records, $model->getData());
            }
			$query = new Query(static::table());
			$query->insert($records);
			return $query->execute();
        }
        else
            return $models->save();
    }

    public static function all(){
		$query = new Query(static::table());
		$query->select()->all()->model(get_called_class());
		return $query->execute();
    }

    public static function where(string $condition = null){
        $query = new Query(static::table());
        $query->select()->model(get_called_class());
        if(!empty($condition))
            $query->where($condition);
        return $query;
    }

    public static function count(){
        $query = new Query(static::table());
        $query->count();
        $result = $query->execute();
        return $result;
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
