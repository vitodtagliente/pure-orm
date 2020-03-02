<?php

/// Copyright (c) Vito Domenico Tagliente
/// Schema definition
/// used to generate database table using the code first approach

namespace Pure\ORM;

use mysql_xdevapi\Exception;

class Schema
{
    /// constructor
    private function __construct()
    {
    }

    /// destructor
    private function __destruct()
    {
    }

    /// Create the database table
    /// @param modelClass - The name of the Model class
    /// @param canSeed - Express if to populate the table after its creation
    /// @return - True if succeed
    public static function create(string $modelClass, bool $canSeed = true): bool
    {
        if (class_exists($modelClass) && is_subclass_of($modelClass, '\Pure\ORM\Model'))
        {
            $rawQuery = $modelClass::schema()->getQuery();
            $query = new Query($rawQuery);
            if ($query->execute())
            {
                if ($canSeed)
                {
                    $modelClass::seed();
                }
                return true;
            }
            return false;
        }
        else
        {
            throw new Exception("$modelClass is not a Pure\ORM\Model class");
            return false;
        }
    }

    /// Drop a table
    /// @param table - the table to drop
    /// @return - true if succeed
    public static function drop(string &$table): bool
    {
        $query = new Query($table);
        $query->drop();
        return $query->execute();
    }

    /// Check if a table exists
    /// @param table - The table
    /// @return - true if exists
    public static function exists(string &$table): bool
    {
        $query = new Query($table);
        $query->exists();
        return $query->execute();
    }

    /// Remove all the rows of a given table
    /// @param table - The table
    /// @return - true if succeed
    public static function clear(string &$table): bool
    {
        $query = new Query($table);
        $query->delete();
        return $query->execute();
    }

    /// Count the number of entries for a specified table
    /// @param table - The table
    /// @param condition - The condition, if any
    /// @return - The count of rows
    public static function count(string &$table, string &$condition = null): integer
    {
        $query = new Query($table);
        $query->count()->where($condition);
        return $query->execute();
    }
}
