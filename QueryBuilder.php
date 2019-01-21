<?php

namespace Pure\ORM;

class QueryBuilder
{
	private function __constructor(){}
	private function __destructor(){}

    // INSERT INTO table_name (column1, column2, column3,...)
    // VALUES (?, ?, ?, ...)
    public static function insert($table, $record)
    {
        if(empty($record)) return null;

        $query = array("INSERT INTO $table");
        array_push($query, ' ('.implode(', ', array_keys($record)).')');
        array_push(
            $query,
            ' VALUES (' .
            implode(', ', array_fill(0, count($record), '?'))
            . ')'
        );
        return implode($query);
    }

    // INSERT INTO table_name (column1, column2, column3,...)
    // VALUES (?, ?, ?, ...), (?, ?, ?, ...), (?, ?, ?, ...), ...
    public static function insertMany($table, $records)
    {
        $count = count($records);
        if($count <= 0) return null;

        $query = array("INSERT INTO $table");
        $record = $records[0];
        array_push($query, ' ('.implode(', ', array_keys($record)).') VALUES ');
        $value = '('.implode(', ', array_fill(0, count($record), '?')).')';
        array_push($query, implode(', ', array_fill(0, $count, $value)));
        return implode($query);
    }

    // UPDATE table_name
    // SET column1=?, column2=?, ...
    // WHERE some_column=some_value
    public static function update($table, $record, $condition = null)
    {
        if(empty($record) || empty($condition)) return null;

        $query = array("UPDATE $table SET ");
        array_push($query, implode('=? , ', array_keys($record)));
        array_push($query, "=? WHERE $condition");
        return implode($query);
    }

    // SELECT column_name(s) FROM table_name
    // WHERE some_column = some_value
    public static function select(
        $table, $fields = array(),
        $condition = null,
        $statement = null)
    {
        $query = array('SELECT ');
        array_push($query, (empty($fields)?'*':implode(', ', $fields)));
        array_push($query, " FROM $table");
        array_push($query, (isset($condition))?" WHERE $condition":'');
        array_push($query, (isset($statement))?" $statement":'');
        return implode($query);
    }

    // DELETE FROM table_name
    // WHERE some_column = some_value
    public static function delete($table, $condition = null)
    {
        $query = array("DELETE FROM $table ");
        array_push($query, (isset($condition))?" WHERE $condition":'');
        return implode($query);
    }

    public static function count($table, $condition = null)
    {
        $query = array("SELECT COUNT(*) FROM $table");
        if(!empty($condition))
            array_push(" WHERE $condition");
        return implode($query);
    }

    public static function drop($table)
    {
        return "DROP TABLE IF EXISTS $table";
    }

    public static function exists($table)
    {
        return "SELECT 1 FROM $table LIMIT 1";
    }
}

?>
