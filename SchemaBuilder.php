<?php

/*
Questa classe permette di definire,
via codice, la struttura su database con cui
andare a definire la tabella del modello specificato.
*/

namespace Pure\ORM;

class SchemaBuilder
{
	private $table = null;
	private $properties = array();
	private $composite_primary = array();

	public function __construct($tablename){
		$this->table = $tablename;
	}

	public function __destruct(){}

	public function get($property){
		if(array_key_exists($property, $this->properties)){
			return $this->properties[$property];
		}
		return null;
	}

	public function add($property ,$type){
		if(!array_key_exists($property, $this->properties)){
			$this->properties[$property] = new SchemaPropertyDescriptor($property, $type);
		}
		return $this->properties[$property];
	}

	public function id($name = 'id'){ return $this->integer($name)->primary()->increments(); }

	public function boolean($name){ return $this->add($name, 'BOOL'); }

	public function integer($name){ return $this->add($name, 'INT'); }

	public function float($name){ return $this->add($name, 'FLOAT'); }

	public function char($name, $size = 30){ return $this->add($name, "VARCHAR($size)"); }

	public function text($name){ return $this->add($name, 'TEXT'); }

	public function date($name){ return $this->add($name, 'DATE'); }

	public function time($name){ return $this->add($name, 'TIME'); }

	public function datetime($name){ return $this->add($name, 'DATETIME'); }

	public function timestamps(){ $this->datetime('created_at'); $this->datetime('updated_at');	}

	// make a composite primary key
	public function primary($names = array()){
		// TODO: chiave primaria composta
	}

	// ritorna il nome di tutte le properties
	public function getNames(){
		return array_keys($this->properties);
	}

	// ritorna tutte le proprietà dello schema
	public function getProperties(){
		return $this->properties;
	}

	public function getQuery(){
		$query = array();
		array_push($query, "CREATE TABLE " . $this->table . " (");
		$comma = '';
		foreach($this->properties as $name => $descriptor)
		{
			array_push($query, "$comma\n\t" . $descriptor->getQueryStatements($this->table));
			$comma = ',';
		}
		array_push($query, "\n)");
		return implode($query);
	}
}
