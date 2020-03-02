<?php

/// Copyright (c) Vito Domenico Tagliente
/// This class helps to build queries

namespace Pure\ORM;

class QueryBuilder
{
    /// constructor
    private function __constructor()
    {
    }

    /// destructor
    private function __destructor()
    {
    }

    /// Generate the query responsible of insert a record
    /// @param table - The name of the table
    /// @param record - The associative array representing the object to insert
    /// @return - The query in PDO format
    /// INSERT INTO table (column1, column2, column3,...) VALUES (?, ?, ?, ...)
    public static function insert(string &$table, array &$record) : string
    {
        if(!(array_keys($record) !== range(0, count($record) - 1)))
        {
            // the record is not an associative array
            return null;
        }

        $query = array("INSERT INTO $table");
        array_push($query, ' (' . implode(', ', array_keys($record)) . ')');
        array_push($query, ' VALUES (' .
            implode(', ', array_fill(0, count($record), '?'))
            . ')'
        );
        return implode($query);
    }

    /// Generate the query responsible of insert records
    /// @param table - The name of the table
    /// @param records - The list of object to insert
    /// @return - The query in PDO format
    /// INSERT INTO table (column1, column2, column3,...) VALUES (?, ?, ?, ...), ..., (?, ?, ?, ...)
    public static function insertMany(string &$table, array &$records) : string
    {
        $count = count($records);
        if ($count <= 0)
        {
            // The array is empty
            return null;
        }

        $query = array("INSERT INTO $table");
        $record = $records[0];

        if(!(array_keys($record) !== range(0, count($record) - 1)))
        {
            // the record is not an associative array
            return null;
        }

        array_push($query, ' (' . implode(', ', array_keys($record)) . ') VALUES ');
        $value = '(' . implode(', ', array_fill(0, count($record), '?')) . ')';
        array_push($query, implode(', ', array_fill(0, $count, $value)));
        return implode($query);
    }

    /// Generate the query responsible of updating a record
    /// @param table - The table name
    /// @param record - the record to update
    /// @param condition - The condition, if any
    /// @return - The query in PDO format
    /// UPDATE table SET column1=?, column2=?, ... WHERE condition
    public static function update(string &$table, array &$record, string &$condition) : string
    {
        if(!(array_keys($record) !== range(0, count($record) - 1)))
        {
            // the record is not an associative array
            return null;
        }

        if (empty($condition))
        {
            // The condition cannot be empty
            return null;
        }

        $query = array("UPDATE $table SET ");
        array_push($query, implode('=? , ', array_keys($record)));
        array_push($query, "=? WHERE $condition");
        return implode($query);
    }

    /// Generate the query responsible of select rows from a table
    /// @param table - The table
    /// @param fields - The fields to retrieve, if empty all the fields will be retrieved
    /// @param condition - The condition, if any
    /// @param statement - The statement, if any
    /// SELECT column_name(s) FROM table WHERE condition statement
    public static function select(
        string &$table,
        array &$fields = array(),
        string &$condition = null,
        string &$statement = null) : string
    {
        $query = array('SELECT ');
        array_push($query, (empty($fields) ? '*' : implode(', ', $fields)));
        array_push($query, " FROM $table");
        array_push($query, (isset($condition)) ? " WHERE $condition" : '');
        array_push($query, (isset($statement)) ? " $statement" : '');
        return implode($query);
    }

    /// Generate the query responsible of removing rows from a table
    /// @param table - The table
    /// @param condition - The condition, if any
    /// @return - The query
    /// DELETE FROM table WHERE condition
    public static function delete(string &$table, string &$condition = null) : string
    {
        $query = array("DELETE FROM $table ");
        array_push($query, (isset($condition)) ? " WHERE $condition" : '');
        return implode($query);
    }

    /// Generate the query responsible of count a table rows
    /// @param table - The table
    /// @param condition - The condition, if any
    /// @return - The query
    public static function count(string &$table, string &$condition = null) : string
    {
        $query = array("SELECT COUNT(*) FROM $table");
        if (!empty($condition))
        {
            array_push($query, " WHERE $condition");
        }
        return implode($query);
    }

    /// Generate the query able to drop a table
    /// @param table - The table
    /// @return - The query
    public static function drop(string &$table) : string
    {
        return "DROP TABLE IF EXISTS $table";
    }

    /// Generate the query that check if a table exists
    /// @param table - The table
    /// @return - The query
    public static function exists(string &$table) : string
    {
        return "SELECT 1 FROM $table LIMIT 1";
    }
}
