<?php

namespace Pure\ORM;

class SchemaBuilder {
	private $columns = array();
	private $name;
	private $primary = array();
	private $foreign = array();
	private $unique = array();
	private $increment;

	public function __construct( $name ){
		$this->name = $name;
	}

	public function add( $name, $type, $expression = null ){
		$this->columns[$name] = array(
			'type' => $type,
			'expression' => $expression
		);
	}

	public function primary( $column ){
		if( is_array( $column ) ){
			$name = "pk_";
			foreach( $column as $field )
				$name .= ( $field );
			$values = $this->arrayToString( $column );
			array_push( $this->primary, "CONSTRAINT $name PRIMARY KEY ( $values )" );
		}
		else array_push( $this->primary, "CONSTRAINT pk_$column PRIMARY KEY ( $column )" );
	}

	public function increments( $column ){
		if( isset( $this->columns[ $column ] ) ){
			$this->columns[ $column ]['increment'] = 'auto_increment';
			if( strpos( strtolower( $this->columns[ $column ][ 'expression' ] ), 'not null' ) === false )
				$this->columns[ $column ][ 'expression' ] .= ' not null';
		}
	}

	public function unique( $column ){
		if( is_array( $column ) ){
			$name = "uc_";
			foreach( $column as $field )
				$name .= ( $field );
			$values = $this->arrayToString( $column );
			array_push( $this->unique, "CONSTRAINT $name UNIQUE ( $values )" );
		}
		else array_push( $this->unique, "CONSTRAINT uc_$column UNIQUE ( $column )" );
	}

	public function foreign( $column, $field ){
		if( isset( $this->columns[ $column ] ) )
			array_push( $this->foreign, "CONSTRAINT fk_" . $column . date('Ymdhms') . " FOREIGN KEY ( $column ) REFERENCES $field" );
	}

	public function query(){
		$query = "CREATE TABLE " . $this->name . " (";
		$add = '';
		foreach ($this->columns as $field => $value) {
			$query .= ( "$add $field $value[type] $value[expression]" );
			if( isset( $value['increment'] ) )
				$query .= (" $value[increment] ");
			$add = ',';
		}
		foreach ($this->primary as $p) {
			$query .= ", $p";
		}
		foreach ($this->foreign as $f) {
			$query .= ( ", $f" );
		}
		foreach ($this->unique as $u) {
			$query .= ( ", $u" );
		}
		$query .= ' )';

		return $query;
	}

	private function arrayToString( $v ){
		$string = "";
		$add = '';
		foreach ($v as $value) {
			$string .= ( "$add $value" );
			$add = ',';
		}
		return $string;
	}

	public function __destruct(){

	}
}

?>
