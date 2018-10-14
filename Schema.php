<?php

namespace Pure\ORM;

class Schema {

	private function __construct(){}

	public static function create($model_class){
		if(class_exists($model_class) && is_subclass_of($model_class, '\Pure\ORM\Model')){
			return Database::main()->execute( $model_class::schema()->query() );
		}
		else 
		{
			error_log("$model_class is not a Pure\ORM\Model class");
			return false;
		}
	}

	public static function drop($model_class){		
		if(class_exists($model_class) && is_subclass_of($model_class, '\Pure\ORM\Model')){
			return Database::main()->execute('DROP TABLE IF EXISTS ' . $model_class::table());
		}
		else 
		{
			error_log("$model_class is not a Pure\ORM\Model class");
			return false;
		}
	}

	public static function exists( $model_class ){
		if(class_exists($model_class) && is_subclass_of($model_class, '\Pure\ORM\Model')){
			return Database::main()->execute('SELECT 1 FROM ' . $model_class::table() . ' LIMIT 1');
		}
		else 
		{
			error_log("$model_class is not a Pure\ORM\Model class");
			return false;
		}
	}

	public static function clear($model_class){
		if(class_exists($model_class) && is_subclass_of($model_class, '\Pure\ORM\Model')){
			return Database::main()->execute('DELETE FROM ' . $model_class::table());
		}
		else 
		{
			error_log("$model_class is not a Pure\ORM\Model class");
			return false;
		}
	}

	private function __destruct(){}
}

?>
