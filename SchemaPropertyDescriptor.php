<?php

/*
Questa classe permette la descrizione 
delle proprietà di un modello.
Tali caratteristiche varranno poi utilizzate
al fine di permettere:
- la generazione del codice SQL per la definizione
  dello schema del modello
- Gestire la validazione sul modello stesso
*/

namespace Pure\ORM;

class SchemaPropertyDescriptor
{
	private $name = null;
	private $type = null;
	private $default_value = null;
	private $auto_increments = false;
	private $nullable = false;
	private $primary = false;
	private $unique = false;
	private $unsigned = false;
	private $foreign = false;
	private $foreign_class = null;
	private $foreign_property = null;

	public function __construct($name, $type){
		$this->name = $name;
		$this->type = $type;
	}

	public function __destruct(){}

	public function default($value){ $this->default_value = $value; return $this; }

	public function increments(){ $this->auto_increments = true; return $this; }

	public function nullable(){ $this->nullable = true; return $this; }

	public function primary(){ $this->primary = true; return $this; }

	public function unique(){ $this->unique = true; return $this;	}

	public function unsigned(){ $this->unsigned = true; return $this; }

	public function link($model_class, $property = 'id'){
		if(class_exists($model_class) && subclass_of($model_class, '\Pure\ORM\Model') && isset($property))
		{
			$this->foreign = true;
			$this->foreign_class = $model_class;
			$this->foreign_property = $property;
		}
		return $this;
	}

	// ottieni il valore di default
	public function get_default(){ return $this->default_value; }

	// ritorna true, se la proprietà è una chiave primaria
	public function is_primary(){ return $this->primary; }

	// ritorna il tipo
	public function get_type(){ return $this->type; }

	// query statement generation
	public function query_statements($table){
		// field declaration
		$query = array();
		array_push($query, $this->name . ' ' . $this->type);
		if($this->nullable == false)
			array_push($query, ' NOT NULL');
		if(isset($this->default_value))
			array_push($query, ' DEFAULT \'' . $this->default_value . '\'');
		if($this->auto_increments && $this->type == 'INT')
			array_push($query, ' AUTO_INCREMENT');
		// constraints
		if($this->primary)
			array_push($query, ",\n\t" . 'CONSTRAINT PK_' . $this->name . ' PRIMARY KEY (' . $this->name . ')');
		if($this->unique)
			array_push($query, ",\n\t" . 'CONSTRAINT UC_' . $this->name . ' UNIQUE KEY (' . $this->name . ')');
		if($this->foreign)
			array_push($query, ",\n\tCONSTRAINT FK_$table" . $this->foreign_class . '_' . $this->name .
				' FOREIGN KEY (' . $this->name . ") REFERENCES $table(" . $this->foreign_property . ')');

		return implode($query);
	}
}

?>