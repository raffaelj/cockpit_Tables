<?php

namespace Tables\Helpers;

/**
 * source of the core of this class:
 * https://phpdelusions.net/pdo/pdo_wrapper#static_instance - Thanks ;-)
 * 
 * If you want to write your own PDO wrapper, don't do it.
 * Read these articles instead:
 *
 * https://phpdelusions.net/pdo/common_mistakes
 * https://phpdelusions.net/pdo/pdo_wrapper
 * https://phpdelusions.net/pdo (the long answer)
 *
 */

class Database
{
    protected static $instance;
    protected $pdo;

    // protected function __construct() {
    public function __construct() { // must be public to add it as a Lime Helper, to do: make it protected again
        $opt  = array(
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
            \PDO::ATTR_EMULATE_PREPARES   => true, // enable to reuse params multiple times
        );
        $dsn = 'mysql:host='.COCKPIT_TABLES_DB_HOST.';dbname='.COCKPIT_TABLES_DB_NAME.';charset='.COCKPIT_TABLES_DB_CHAR;
        
        $this->pdo = new \PDO($dsn, COCKPIT_TABLES_DB_USER, COCKPIT_TABLES_DB_PASS, $opt);
    }

    // a classical static method to make it universally available
    public static function instance()
    {
        if (self::$instance === null)
        {
            self::$instance = new self;
        }
        return self::$instance;
    }

    // a proxy to native \PDO methods
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->pdo, $method), $args);
    }

    // a helper function to run prepared statements smoothly
    public function run($sql, $args = [])
    {
        if (!$args)
        {
             return $this->query($sql);
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($args);
        return $stmt;
    }

    // end of borrowed code snippet

    // useful helper to find foreign key relations
    public function listRelations($table = null, $column = null) {

        $parts[] = "SELECT";
        $parts[] = "`TABLE_NAME`";
        $parts[] = ",`COLUMN_NAME`";
        $parts[] = ",`REFERENCED_TABLE_NAME`";
        $parts[] = ",`REFERENCED_COLUMN_NAME`";
        $parts[] = "FROM `information_schema`.`key_column_usage`";
        $parts[] = "WHERE";
        $parts[] = "`REFERENCED_TABLE_NAME` IS NOT NULL";

        $parts[] = "AND `table_schema` = :database";
        $params[':database'] = COCKPIT_TABLES_DB_NAME;

        if ($table) {
            $parts[] = "AND `TABLE_NAME` = :table";
            $params[':table'] = $table;
        }

        if ($column) {
            $parts[] = "AND `COLUMN_NAME` = :column";
            $params[':column'] = $column;
        }

        $query = implode(' ', $parts);

        return $this->run($query, $params)->fetchAll(\PDO::FETCH_ASSOC);

    }
    
    public function listTables($type = 'table') {

        $table_type = $type == 'view' ? 'VIEW' : 'BASE TABLE';

        $parts[] = "SELECT `TABLE_NAME`";
        $parts[] = "FROM `information_schema`.`TABLES`";
        $parts[] = "WHERE `TABLE_SCHEMA` = :database";
        $parts[] = "AND `TABLE_TYPE` = :table_type";

        $query = implode(' ', $parts);

        $params = [
            ':database' => COCKPIT_TABLES_DB_NAME,
            ':table_type' => $table_type,
        ];

        $tables = $this->run($query, $params)->fetchAll(\PDO::FETCH_COLUMN);

        return $tables;

    }

}
