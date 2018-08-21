<?php

namespace Pure\ORM;

abstract class Model
{
    // ritorna il nome della tabella
    // che corrisponde al nome della classe del modello in minuscolo
    // resa al plurale
    // example: User -> users
    public static function table(){
        $path = explode('\\', get_called_class());
        return array_pop($path) . 's';
    }

    private $fields = [];
    private $identifiers = [];

    protected function id( $id ){
        if( is_array($id) ){
            foreach ($id as $field) {
                if( array_key_exists($field, $this->fields) && !in_array($field, $this->identifiers) )
                    array_push( $this->identifiers, $field );
            }
        }
        else {
            if( !in_array($id, $this->identifiers) )
                array_push( $this->identifiers, $id );
        }
    }

    private function getId(){
        $value = '';
        $and = '';
        foreach ($this->identifiers as $id) {
            $value .= "$and $id = '".$this->fields[$id]."' ";
            $and = 'AND';
        }
        return $value;
    }

    protected function field( $name, $default = null){
        $this->fields[$name] = $default;
    }

    public function __get( $index ){
		return $this->fields[$index];
	}

	public function __set( $index, $value ){
        if( array_key_exists( $index, $this->fields ) )
		      $this->fields[$index] = $value;
	}

    public function columns() {
        $fields = [];
        foreach($this->fields as $key => $value)
            array_push($fields, $key);
        return $fields;
    }

    public function toArray(){
        return $this->fields;
    }

    public function exists(){
        foreach ($this->identifiers as $id) {
            if( !isset($this->fields[$id]) )
                return false;
        }

        return true;
    }

    public function save(){
        if( $this->exists() ){
            // update
            $result = false;
            $id = $this->getId();
            if(isset($id))
                $result = Database::main()->update( self::table(), $id, $this->toArray());

            return $result;
        }
        else {
            // insert
            $result = Database::main()->insert( self::table(), $this->toArray() );
            if( $result ){

            }
            return $result;
        }
    }

    public function erase(){
        if( !$this->exists() )
            return false;
        return Database::main()->delete(self::table(), $this->getId());
    }

    public static function find($where){
        $classname = get_called_class();
        $model = new $classname();

        $result = Database::main()->select( self::table(), null, $where );
        if(!$result)
            return null;

        foreach( $result as $key => $value )
            $model->$key = $value;
        return $model;
    }

    public static function all($where = null){
        $classname = get_called_class();
        $temp = new $classname();
        $models = [];

        $result = Database::main()->selectAll( self::table(), null, $where );
        if(!empty($result))
        {
            foreach ($result as $r)
            {
                $model = new $classname();
                foreach( $r as $key => $value )
                    $model->$key = $value;
                array_push( $models, $model );
            }
        }
        return $models;
    }
}

?>
