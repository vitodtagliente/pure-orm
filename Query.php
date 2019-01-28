<?php

namespace Pure\ORM;

class Query
{
    public const TYPE_NULL = null;
    public const TYPE_COUNT = 'COUNT';
    public const TYPE_DELETE = 'DELETE';
    public const TYPE_DROP = 'DROP';
    public const TYPE_EXISTS = 'EXISTS';
    public const TYPE_INSERT = 'INSERT';
    public const TYPE_SELECT = 'SELECT';
    public const TYPE_UPDATE = 'UPDATE';

    private $table = null;
    private $type = self::TYPE_NULL;
    private $select_all = false;
    private $data = null;
    private $condition = null;
    private $limit = null;
    private $offset = null;
    private $order = null;
    private $statement = null;
    private $order_asc = false;
    private $model = null;

    private $success = false;
    private $error_message = null;

    private static $report_errors = false;

    public static function error_reporting($value = true){
        self::$report_errors = $value;
    }

    public function __construct($table){
        if(!isset($table)) exit("Invalid query table reference.");
        $this->table = $table;
    }

    public function all(){
        if($this->isSelectQuery())
            $this->select_all = true;
        return $this;
    }

    public function count(){
        $this->clearType();
        $this->type = self::TYPE_COUNT;
        return $this;
    }

    public function delete(){
        $this->clearType();
        $this->type = self::TYPE_DELETE;
        return $this;
    }

    public function drop(){
        $this->clearType();
        $this->type = self::TYPE_DROP;
        return $this;
    }

    public function exists(){
        $this->clearType();
        $this->type = self::TYPE_EXISTS;
        return $this;
    }

    public function insert(array $data){
        $this->data = $data;
        $this->clearType();
        $this->type = self::TYPE_INSERT;
        return $this;
    }

    public function limit(int $max, int $start = 0){
        $this->limit = $max;
        $this->offset = $start;
        return $this;
    }

    public function model(string $name){
        $this->model = $name;
        return $this;
    }

    public function order(string $column, bool $order_asc = true){
        $this->order = $column;
        $this->order_asc = $order_asc;
        return $this;
    }

    public function select(array $data = array()){
        $this->data = $data;
        $this->clearType();
        $this->type = self::TYPE_SELECT;
        return $this;
    }

    public function statement(string $string){
        $this->statement = $string;
        return $this;
    }

    public function update(array $data, string $condition = null){
        $this->data = $data;
        $this->condition = $condition;
        $this->clearType();
        $this->type = self::TYPE_UPDATE;
        return $this;
    }

    public function where(string $condition){
        $this->condition = $condition;
        return $this;
    }

    public function or(string $condition){
        if(isset($this->condition) && !empty($this->condition))
            $this->condition .= " OR $condition";
        else $this->condition = $condition;
        return $this;
    }

    public function and(string $condition){
        if(isset($this->condition) && !empty($this->condition))
            $this->condition .= " AND $condition";
        else $this->condition = $condition;
        return $this;
    }

    private function clearType(){
        $this->type = self::TYPE_NULL;
        $this->select_all = false;
    }

    public function getType(){ return $this->type; }
    public function isCountQuery(){ return $this->type == self::TYPE_COUNT; }
    public function isDeleteQuery(){ return $this->type == self::TYPE_DELETE; }
    public function isDropQuery(){ return $this->type == self::TYPE_DROP; }
    public function isExistsQuery(){ return $this->type == self::TYPE_EXISTS; }
    public function isInsertQuery(){ return $this->type == self::TYPE_INSERT; }
    public function isSelectQuery(){ return $this->type == self::TYPE_SELECT; }
    public function isUpdateQuery(){ return $this->type == self::TYPE_UPDATE; }
    public function isValid(){ return $this->type != self::TYPE_NULL; }

    public function getErrorMessage(){ return $this->error_message; }
    public function success(){ return $this->success; }

    public function execute(string $in_query = null){
        $db = Database::main();
        if(isset($db) && $db->isConnected())
        {
            $pdo = $db->getPdo();
            $query = $this->getQuery();
            $values = $this->getValues();
            try
            {
                $stmt = $pdo->prepare($query);
                $this->success = $stmt->execute($values);
                if($this->success)
                {
                    if($this->isSelectQuery())
                    {
                        $fetch = null;
                        if($this->select_all)
                            $fetch = $stmt->fetchAll();
                        else $fetch = $stmt->fetch();

                        // generate models
                        if(isset($this->model))
                        {
                            $model_class = $this->model;
                            if(class_exists($model_class) && is_subclass_of($model_class, '\Pure\ORM\Model'))
                            {
                                if($this->select_all)
                                {
                                    $models = array();
                                    foreach ($fetch as $record) {
                                        array_push($models, new $model_class($record, true));
                                    }
                                    return $models;
                                }
                                else 
                                {
                                    return new $model_class($fetch, true);
                                }
                            }
                            else exit("$model_class is not a Pure\ORM\Model class");
                        }
                        // return the pure fetch
                        return $fetch;
                    }
                    else if($this->isCountQuery())
                    {
                        $fetch = $stmt->fetch();
                        return $fetch['COUNT(*)'];
                    }
                }
                return $this->success;
            }
            catch(\PDOException $e){
                $this->error_message = $query."\n".$e->getMessage();
                if(self::$report_errors)
                    echo exit("Query error: " . $this->error_message);
                return false;
            }
        }
        else exit("Cannot execute the query. Invalid Database connection.");
    }

    private function getQuery(){
        if($this->isCountQuery()){
            return QueryBuilder::count($this->table, $this->condition);
        }
        else if($this->isDeleteQuery()){
            return QueryBuilder::delete($this->table, $this->condition);
        }
        else if($this->isDropQuery()){
            return QueryBuilder::drop($this->table, $this->condition);
        }
        else if($this->isExistsQuery()){
            return QueryBuilder::exists($this->table);
        }
        else if($this->isInsertQuery()){
            // Are many records?
            if(isset($this->data[0]))
                return QueryBuilder::insertMany($this->table, $this->data);
            return QueryBuilder::insert($this->table, $this->data);
        }
        else if($this->isSelectQuery()){
            return QueryBuilder::select(
                $this->table,
                $this->data,
                $this->condition,
                $this->getStatement()
            );
        }
        else if($this->isUpdateQuery()){
            return QueryBuilder::update($this->table, $this->data, $this->condition);
        }
        else return $this->table;
    }

    private function getValues(){
        if($this->isDropQuery()) return array();
        else if($this->isDeleteQuery()) return array();
        else if($this->isCountQuery()) return array();
        else if($this->isExistsQuery()) return array();
        else if($this->isInsertQuery()){
            // Are many records?
            if(isset($this->data[0]))
            {
                $values = array();
                foreach ($this->data as $record) {
                    $values = array_merge($values, $this->sanitize(array_values($record)));
                }
                return $values;
            }
            return $this->sanitize(array_values($this->data));           
        }
        else if($this->isSelectQuery()){
            return array();
        }
        else if($this->isUpdateQuery()){
            return $this->sanitize(array_values($this->data));
        }
        else return array();
    }

    private function sanitize($data = array()){
        for ($i = 0; $i < count($data); $i++) {
            if(is_bool($data[$i]))
                $data[$i] = ($data[$i])?1:0;
        }
        return $data;
    }

    private function getStatement(){
        $st = array($this->statement);
        $mode = 'ASC';
        if(isset($this->order)){
            $mode = ($this->order_asc)?'ASC':'DESC';
            array_push($st, 'ORDER BY ' . $this->order . " $mode");
        }
        if(isset($this->limit))
            array_push($st, 'LIMIT ' . $this->limit . ' OFFSET ' . $this->offset);
        return implode(' ', $st);
    }

    public function info(){
        var_dump($this);
    }
};

?>
