<?php

/*
    How To:

    Connecting to DataBase
    $db = new Database("mysql", "localhost", "database", "root", "password", array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

    Getting row
    $db->fetch("SELECT email, username FROM users WHERE username =?", array("aaaa"));
    $db->select("users", array( 'email', 'username' ), "username = aaaa" );

    Getting multiple rows
    $db->fetchAll("SELECT id, username FROM users");

    inserting a row
    $db->insert("users", array( "name" => "aaaa", "email" => "aaaa@email.com"));

    updating existing row
    $db->update("users", "id = 1", array( "name" => "aaaa1" ) );

*/

namespace Pure\ORM;

class Database {
    private $context = null, $connected = false;
    public $debug = false;
    private static $pdo_bind = null;

    private static $instance, $config;

    function __construct( $type, $host, $dbname, $username, $password, $options = array() ){
        $this->connected = true;

        if(isset(self::$pdo_bind))
            return;

        try {
            $this->context = new \PDO(
                "$type:host={$host};dbname={$dbname};charset=utf8", $username, $password, $options
            );
            $this->context->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
            $this->context->setAttribute( \PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC );
        }
        catch( \PDOException $e ) {
            if( $this->debug )
                throw new Exception( $e->getMessage() );
            $this->connected = false;
        }
    }

    // <------------- STATIC FUNCTIONS

    public static function prepare( $type, $host, $dbname, $username, $password, $options = array() ){
    	self::$config = array(
            'type' => $type,
            'host' => $host,
            'name' => $dbname,
            'user' => $username,
            'pass' => $password,
            'options' => $options
        );
    }

    public static function bind($pdo){
        if( !isset($pdo) )
            return null;
        self::$pdo_bind = $pdo;

        $db = new self();

        self::$pdo_bind = null;

        return $db;
    }

    public static function change( $db ){
        if( is_object( $db ) )
            self::$instance = $db;
    }

    public static function main(){
        if( !isset( self::$instance ) && is_array( self::$config ) ){
        	self::$instance = new Database(
        		self::$config['type'], self::$config['host'], self::$config['name'],
                self::$config['user'], self::$config['pass'], self::$config['options']
        	);
        }
        return self::$instance;
    }

    public static function end(){
    	if( isset( self::$instance ) )
    		self::main()->close();
    }

    // ------------->

    function isConnected(){
        return $this->connected;
    }

    function pdo(){
        return $this->context;
    }

    function fetch( $query, $params = array() ){
        try{
            $stmt = $this->context->prepare( $query );
            $stmt->execute( $params );
            return $stmt->fetch();
        }
        catch( \PDOException $e ){
            if( $this->debug )
                echo( $e->getMessage() );
            return false;
        }
    }

    function fetchAll( $query, $params = array() ){
        try{
            $stmt = $this->context->prepare( $query );
            $stmt->execute( $params );
            return $stmt->fetchAll();
        }
        catch( \PDOException $e ){
            if( $this->debug )
                echo( $e->getMessage() );
            return false;
        }
    }

    function execute( $query, $params = array() ){
        try{
            $stmt = $this->context->prepare( $query );
            $res = $stmt->execute( $params );
            return $res;
        }
        catch( \PDOException $e ){
            if( $this->debug )
                echo( $e->getMessage() );
            return false;
        }
    }

    function insert( $table, $params = array() ){
        $query = "INSERT INTO $table ";
        $values = array();
        $add = '(';
        $part1 = ''; $part2 = '';
        foreach ($params as $key => $value) {
            $part1 .= "$add $key";
            $part2 .= "$add ?";
            $add = ',';
            array_push( $values, $value );
        }
        if( count($values) > 0 ){
            $part1 .= ' )';
            $part2 .= ' )';
        }
        $query .= ( $part1 . ' VALUES ' . $part2 );

        if( $this->debug )
            echo( $query );

        return $this->execute( $query, $values );
    }

    function update( $table, $where = null, $params = array() ){
        $query = "UPDATE $table ";
        $values = array();
        $add = 'SET';
        $part = '';
        foreach ($params as $key => $value) {
            $part .= "$add $key = ?";
            $add = ',';
            array_push( $values, $value );
        }
        if( isset( $where ) ){
            $part .= ( ' WHERE ' . $where );
        }
        $query .= $part;

        if( $this->debug )
            echo( $query );

        return $this->execute( $query, $values );
    }

    function select( $table, $fields = null, $where = null ){
        $query = "SELECT ";
        if( !isset( $fields ) )
            $query .= '* ';
        else {
            if( is_array($fields) ){
                $add = ' ';
                foreach ($fields as $field) {
                    $query .= ( "$add $field" );
                    $add = ',';
                }
            }
            else $query .= ( " $fields " );
        }
        $query .= ( " FROM $table" );
        if( isset( $where ) ){
            $query .= ( " WHERE $where" );
        }

        return $this->fetch( $query );
    }

    function selectAll( $table, $fields = null, $where = null ){
        $query = "SELECT ";
        if( !isset( $fields ) )
            $query .= '* ';
        else {
            if( is_array($fields) ){
                $add = ' ';
                foreach ($fields as $field) {
                    $query .= ( "$add $field" );
                    $add = ',';
                }
            }
            else $query .= ( " $fields " );
        }
        $query .= ( " FROM $table" );
        if( isset( $where ) ){
            $query .= ( " WHERE $where" );
        }

        return $this->fetchAll( $query );
    }

    function delete($table, $where = null){
        $query = null;
        if(isset($where))
            $query = "DELETE FROM $table WHERE $where";
        else $query = "DELETE FROM $table";

        if( $this->debug )
            echo( $query );

        return $this->execute( $query );
    }

    function close(){
        $this->context = null;
        $this->connected = false;
    }

    function __destruct(){
        $this->close();
    }
}


?>
