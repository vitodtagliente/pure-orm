<?php

/// Copyright (c) Vito Domenico Tagliente
///
/// Query implementation

namespace Pure\ORM;

class Query
{
    /// Enums that represent the type of the query
    public const TYPE_COUNT = 'COUNT';
    public const TYPE_DELETE = 'DELETE';
    public const TYPE_DROP = 'DROP';
    public const TYPE_EXISTS = 'EXISTS';
    public const TYPE_INSERT = 'INSERT';
    public const TYPE_NULL = 'NULL';
    public const TYPE_RAW = 'RAW';
    public const TYPE_SELECT = 'SELECT';
    public const TYPE_UPDATE = 'UPDATE';

    /// component that define a query
    /// used to build the query

    /// The table to which the query refer
    private string $m_table = '';
    /// the type of the query
    private string $m_type = self::TYPE_NULL;
    /// Is this a select all query?
    private bool $m_isSelectAll = false;
    /// The query data
    private $m_data = null;
    /// The query condition
    private string $m_condition = '';
    /// Has the query a limit?
    private bool $m_hasLimit = false;
    /// The query limit
    private int $m_limit = 0;
    /// The query offset
    private int $m_offset = 0;
    /// The query order
    private $m_order = null;
    /// The query statement
    private $m_statement = null;
    /// The query order asc
    private $m_orderAsc = false;
    /// The model of the query
    private $m_model = null;
    /// cache any error message
    private $m_errorMessage = null;
    /// The raw query
    private string $m_rawQuery = '';

    private bool $m_success = false;

    /// constructor
    /// @param table - The table
    public function __construct(?string $query = null)
    {
        if (!empty($query))
        {
            $this->m_type = self::TYPE_RAW;
            $this->m_rawQuery = $query;
        }
    }

    /// Set this query as a select all type
    /// valid only if this is a select query
    /// @return - The query
    public function all() : Query
    {
        if ($this->m_type == self::TYPE_SELECT)
        {
            $this->m_isSelectAll = true;
        }
        return $this;
    }

    /// Set this as a count query
    /// @return - The query
    public function count(string $table) : Query
    {
        $this->clear();
        $this->m_type = self::TYPE_COUNT;
        $this->m_table = $table;
        return $this;
    }

    /// Set this as a delete query
    /// @return - The query
    public function delete(string $table) : Query
    {
        $this->clear();
        $this->m_type = self::TYPE_DELETE;
        $this->m_table = $table;
        return $this;
    }

    /// Set this as a drop query
    /// @return - The query
    public function drop(string $table) : Query
    {
        $this->clear();
        $this->m_type = self::TYPE_DROP;
        $this->m_table = $table;
        return $this;
    }

    /// Set this as an exists query
    /// @return - The query
    public function exists(string $table) : Query
    {
        $this->clear();
        $this->m_type = self::TYPE_EXISTS;
        $this->m_table = $table;
        return $this;
    }

    /// Set this as an insert query
    /// @return - The query
    public function insert(string $table, array &$data) : Query
    {
        $this->clear();
        $this->m_type = self::TYPE_INSERT;
        $this->m_table = $table;
        $this->m_data = $data;
        return $this;
    }

    /// Set the limit of the query
    /// @param max - The limit max
    /// @param begin - The limit begin, default zero
    /// @return - The query
    public function limit(int $max, int $begin = 0) : Query
    {
        $this->m_hasLimit = true;
        $this->m_limit = $max;
        $this->m_offset = $begin;
        return $this;
    }

    /// Set the model for the query
    /// This will let to instantiate models based on the query results
    /// usefull for select, select all queries
    /// @param model - The model name
    /// @return - The query
    public function model(string $modelClass) : Query
    {
        if(class_exists($modelClass) && is_subclass_of($modelClass, '\Pure\ORM\Model'))
        {
            $this->m_model = $modelClass;
        }
        else
        {
            throw new \Exception("The class $modelClass is not a valid \Pure\ORM\Model class");
        }
        return $this;
    }

    /// Set the order for a specific column
    /// @param column - the column
    /// @param asc - If the order is asc
    public function order(string $column, bool $asc = true) : Query
    {
        $this->m_order = $column;
        $this->m_orderAsc = $asc;
        return $this;
    }

    /// Set this as a select query
    /// @param data - The data
    /// @return - The query
    public function select(string $table, ?array $data = array()) : Query
    {
        $this->clear();
        $this->m_type = self::TYPE_SELECT;
        $this->m_table = $table;
        $this->m_data = $data;
        return $this;
    }

    /// Set the query statement
    /// @param statement - The statement
    /// @return - The query
    public function statement(string $statement) : Query
    {
        $this->m_statement = $statement;
        return $this;
    }

    /// Set this as an update query
    /// @param data - The data
    /// @param condition - The condition
    /// @return - The query
    public function update(string $table, array $data, ?string $condition = null) : Query
    {
        $this->clear();
        $this->m_type = self::TYPE_UPDATE;
        $this->m_table = $table;
        $this->m_data = $data;
        $this->m_condition = $condition;
        return $this;
    }

    /// Set the condition for the query
    /// @param condition - The condition
    /// @return - The query
    public function where(string $condition) : Query
    {
        $this->m_condition = $condition;
        return $this;
    }

    /// Concatenate conditions
    /// @param condition - The condition
    /// @return - The query
    public function or(string &$condition) : Query
    {
        if (isset($this->m_condition) && !empty($this->m_condition))
        {
            $this->m_condition .= " OR $condition";
        }
        else
        {
            $this->m_condition = $condition;
        }
        return $this;
    }

    /// Concatenate conditions
    /// @param condition - The condition
    /// @return - The query
    public function and(string &$condition) : Query
    {
        if (isset($this->m_condition) && !empty($this->m_condition))
        {
            $this->m_condition .= " AND $condition";
        }
        else
        {
            $this->m_condition = $condition;
        }
        return $this;
    }

    /// Clear the query
    /// @return - The query
    private function clear() : Query
    {
        $this->m_type = self::TYPE_NULL;
        $this->m_isSelectAll = false;
        $this->m_hasLimit = false;
        $this->m_condition = '';
        $this->m_rawQuery = '';
        return $this;
    }

    /// Retrieve the type of the query
    /// @return - The query type
    public function getType() : string
    {
        return $this->m_type;
    }

    /// Check if this is a valid query
    /// @return - True if it is
    public function isValid() : bool
    {
        return $this->m_type != self::TYPE_NULL;
    }

    /// Retrieve the error message
    /// @return - The error message
    public function getErrorMessage() : string
    {
        return $this->error_message;
    }

    /// Return the state of the query
    /// @return - The true if succeeded
    public function success() : bool
    {
        return $this->m_success;
    }

    /// Execute the query
    /// @return - True if succeed
    public function execute()
    {
        $db = Database::main();
        if (isset($db) && $db->isConnected())
        {
            $this->m_errorMessage = null;
            $pdo = $db->getPDO();
            $query = $this->formatQuery();
            $values = $this->formatValues();
            try
            {
                $stmt = $pdo->prepare($query);
                $this->m_success = $stmt->execute($values);
                if ($this->m_success)
                {
                    if ($this->m_type == self::TYPE_SELECT)
                    {
                        $fetch = null;
                        if ($this->m_isSelectAll)
                            $fetch = $stmt->fetchAll();
                        else $fetch = $stmt->fetch();

                        // generate models
                        if (isset($this->m_model))
                        {
                            if(is_array($fetch) == false) return null;

                            $model_class = $this->m_model;
                            if ($this->m_isSelectAll)
                            {
                                $models = array();
                                foreach ($fetch as $record)
                                {
                                    array_push($models, new $model_class($record, true));
                                }
                                return $models;
                            }
                            else
                            {
                                return new $model_class($fetch, true);
                            }
                        }
                        // return the pure fetch
                        return $fetch;
                    }
                    else if ($this->m_type == self::TYPE_COUNT)
                    {
                        $fetch = $stmt->fetch();
                        return $fetch['COUNT(*)'];
                    }
                }
                return true;
            }
            catch (\PDOException $e)
            {
                $this->m_errorMessage = $query . "\n" . $e->getMessage();
            }
            return false;
        }
        else
        {
            throw new \Exception("Cannot execute the query. Invalid Database connection.");
            return false;
        }
    }

    /// Build the query in PDO format
    /// @return - The query
    private function formatQuery() : string
    {
        switch($this->m_type)
        {
            case self::TYPE_COUNT:
                return QueryBuilder::count($this->m_table, $this->m_condition);
                break;
            case self::TYPE_DELETE:
                return QueryBuilder::delete($this->m_table, $this->m_condition);
                break;
            case self::TYPE_DROP:
                return QueryBuilder::drop($this->m_table, $this->m_condition);
                break;
            case self::TYPE_EXISTS:
                return QueryBuilder::exists($this->m_table);
                break;
            case self::TYPE_INSERT:
                {
                    if (isset($this->m_data[0]))
                        return QueryBuilder::insertMany($this->m_table, $this->m_data);
                    return QueryBuilder::insert($this->m_table, $this->m_data);
                }
                break;
            case self::TYPE_SELECT:
                {
                    return QueryBuilder::select(
                        $this->m_table,
                        $this->m_data,
                        $this->m_condition,
                        $this->buildStatements()
                    );
                }
                break;
            case self::TYPE_UPDATE:
                {
                    return QueryBuilder::update($this->m_table, $this->m_data, $this->m_condition);
                }
                break;
            case self::TYPE_RAW:
                return $this->m_rawQuery;
                break;
            default:
                return '';
                break;
        }
    }

    /// Retrieve the values used to set the PDO query format
    /// @return - The values
    private function formatValues() : array
    {
        switch($this->m_type)
        {
            case self::TYPE_INSERT:
                {
                    // Are many records?
                    if (isset($this->m_data[0]))
                    {
                        $values = array();
                        foreach ($this->m_data as $record)
                        {
                            $values = array_merge($values, $this->sanitize(array_values($record)));
                        }
                        return $values;
                    }
                    return $this->sanitize(array_values($this->m_data));
                }
                break;
            case self::TYPE_UPDATE:
                {
                    return $this->sanitize(array_values($this->m_data));
                }
                break;
            default:
                return array();
                break;
        }
    }

    /// Sanitize data
    /// for example boolean are formatted as integers 1 or 0
    /// @param data - The data to sanitize
    /// @return - the sanitized data
    private function sanitize(?array $data = array()) : array
    {
        for ($i = 0; $i < count($data); $i++)
        {
            if (is_bool($data[$i]))
                $data[$i] = ($data[$i]) ? 1 : 0;
        }
        return $data;
    }

    /// Retrieve the query statement
    /// @return - The statement
    private function buildStatements() : string
    {
        $st = array($this->m_statement);
        $mode = 'ASC';
        if (isset($this->m_order))
        {
            $mode = ($this->m_orderAsc) ? 'ASC' : 'DESC';
            array_push($st, 'ORDER BY ' . $this->m_order . " $mode");
        }
        if ($this->m_hasLimit)
        {
            array_push($st, 'LIMIT ' . $this->m_limit . ' OFFSET ' . $this->m_offset);
        }
        return implode(' ', $st);
    }

    /// Debug the query
    public function info() : void
    {
        var_dump($this);
    }
}
