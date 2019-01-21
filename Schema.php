<?php

namespace Pure\ORM;

class Schema {

	private function __construct(){}

	public static function create($model_class, $seed_schema = true){
		if(class_exists($model_class) && is_subclass_of($model_class, '\Pure\ORM\Model')){
			$query = new Query($model_class::schema()->query());
			if($query->execute())
			{
				if($seed_schema)
					$model_class::seed();
				return true;
			}
			return false;
		}
		else
		{
			error_log("$model_class is not a Pure\ORM\Model class");
			return false;
		}
	}

	public static function drop($model_class){
		if(class_exists($model_class) && is_subclass_of($model_class, '\Pure\ORM\Model')){
			$query = new Query($model_class::table());
			$query->drop();
			return $query->execute();
		}
		else
		{
			error_log("$model_class is not a Pure\ORM\Model class");
			return false;
		}
	}

	public static function exists( $model_class ){
		if(class_exists($model_class) && is_subclass_of($model_class, '\Pure\ORM\Model')){
			$query = new Query($model_class::table());
			$query->exists();
			return $query->execute();
		}
		else
		{
			error_log("$model_class is not a Pure\ORM\Model class");
			return false;
		}
	}

	public static function clear($model_class){
		if(class_exists($model_class) && is_subclass_of($model_class, '\Pure\ORM\Model')){
			$query = new Query($model_class::table());
			$query->delete();
			return $query->execute();
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
