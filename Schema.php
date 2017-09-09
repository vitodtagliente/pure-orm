<?php

namespace Pure\ORM;

class Schema {

	private function __construct(){}

	public static function create( $argument ){
		if( is_callable( $argument ) )
			$argument = $argument();
		if( is_object( $argument ) )
			return Database::main()->execute( $argument->query() );
		return Database::main()->execute( $argument );
	}

	public static function drop( $table ){
		return Database::main()->execute( "DROP TABLE IF EXISTS $table" );
	}

	public static function exists( $table ){
		return Database::main()->execute( "SELECT 1 FROM $table LIMIT 1" );
	}

	private function __destruct(){}
}

?>
