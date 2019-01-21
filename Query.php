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

    private $success = false;
    private $error_message = null;

    public function __construct($table){
        if(!isset($table)) exit("Invalid query table reference.");
        $this->table = $table;
    }

    public function all(){
        if($this->is_select())
            $this->select_all = true;
        return $this;
    }

    public function count(){
        $this->clear_type();
        $this->type = self::TYPE_COUNT;
        return $this;
    }

    public function delete(){
        $this->clear_type();
        $this->type = self::TYPE_DELETE;
        return $this;
    }

    public function exists(){
        $this->clear_type();
        $this->type = self::TYPE_EXISTS;
        return $this;
    }

    public function insert($data){
        $this->data = $data;
        $this->clear_type();
        $this->type = self::TYPE_INSERT;
        return $this;
    }

    public function limit($max, $start = 0){
        $this->limit = $max;
        $this->offset = $start;
        return $this;
    }

    public function order($column, $order_asc = true){
        $this->order = $column;
        $this->order_asc = $order_asc;
        return $this;
    }

    public function select($data = array()){
        $this->data = $data;
        $this->clear_type();
        $this->type = self::TYPE_SELECT;
        return $this;
    }

    public function statement($string){
        $this->statement = $string;
        return $this;
    }

    public function update($data, $condition = null){
        $this->data = $data;
        $this->condition = $condition;
        $this->clear_type();
        $this->type = self::TYPE_UPDATE;
        return $this;
    }

    public function where($condition){
        $this->condition = $condition;
        return $this;
    }

    private function clear_type(){
        $this->type = self::TYPE_NULL;
        $this->select_all = false;
    }

    public function get_type(){ return $this->type; }
    public function is_count(){ return $this->type == self::TYPE_COUNT; }
    public function is_delete(){ return $this->type == self::TYPE_DELETE; }
    public function is_drop(){ return $this->type == self::TYPE_DROP; }
    public function is_exists(){ return $this->type == self::TYPE_EXISTS; }
    public function is_insert(){ return $this->type == self::TYPE_INSERT; }
    public function is_select(){ return $this->type == self::TYPE_SELECT; }
    public function is_update(){ return $this->type == self::TYPE_UPDATE; }
    public function is_valid(){ return $this->type != self::TYPE_NULL; }

    public function get_error(){ return $this->error_message; }
    public function success(){ return $this->success; }

    public function execute($in_query = null){
        $db = Database::main();
        if(isset($db) && $db->is_connected())
        {
            $pdo = $db->pdo();
            $query = $this->get_query();
            $values = $this->get_values();
            try
            {
                $stmt = $pdo->prepare($query);
                $this->success = $stmt->execute($values);
                if($this->success)
                {
                    if($this->is_select() || $this->is_count())
                    {
                        if($this->select_all)
                            return $stmt->fetchAll();
                        $fetch = $stmt->fetch();
                        if($this->is_count())
                            return $fetch['COUNT(*)'];
                        return $fetch;
                    }
                }
                return $this->success;
            }
            catch(\PDOException $e){
                $this->error_message = $query."\n".$e->getMessage();
                return false;
            }
        }
        else exit("Cannot execute the query. Invalid Database connection.");
    }

    private function get_query(){
        if($this->is_count()){
            return QueryBuilder::count($this->table, $this->condition);
        }
        else if($this->is_delete()){
            return QueryBuilder::delete($this->table, $this->condition);
        }
        else if($this->is_drop()){
            return QueryBuilder::drop($this->table, $this->condition);
        }
        else if($this->is_exists()){
            return QueryBuilder::exists($this->table);
        }
        else if($this->is_insert()){
            // Are many records?
            if(isset($this->data[0]))
                return QueryBuilder::insertMany($this->table, $this->data);
            return QueryBuilder::insert($this->table, $this->data);
        }
        else if($this->is_select()){
            return QueryBuilder::select(
                $this->table,
                $this->data,
                $this->condition,
                $this->get_statement()
            );
        }
        else if($this->is_update()){
            return QueryBuilder::update($this->table, $this->data, $this->condition);
        }
        else return $this->table;
    }

    private function get_values(){
        if($this->is_drop()) return array();
        else if($this->is_delete()) return array();
        else if($this->is_count()) return array();
        else if($this->is_exists()) return array();
        else if($this->is_insert()){
            return $this->sanitize(array_values($this->data));
        }
        else if($this->is_select()){
            return array();
        }
        else if($this->is_update()){
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

    private function get_statement(){
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
