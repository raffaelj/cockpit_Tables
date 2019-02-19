<?php

// simple logging for debugging
function debug($message) {

    global $cockpit;

    if (!is_string($message) || !is_numeric($message))
        $message = json_encode($message);

    $time = date('Y-m-d H:i:s', time());

    $cockpit('fs')->write("#storage:tmp/.log.txt", "$time - $message\r\n", FILE_APPEND);

}

// load database config

$db_config = $app->retrieve('tables/db', []);

$db_config = array_merge([
      'host' => 'localhost'
    , 'database' => ''
    , 'user' => 'root'
    , 'password' => ''
    , 'prefix' => ''
    , 'charset' => 'utf8'
    ], $db_config
);

define('COCKPIT_TABLES_DB_HOST', $db_config['host']);
define('COCKPIT_TABLES_DB_NAME', $db_config['database']);
define('COCKPIT_TABLES_DB_CHAR', $db_config['charset']);
define('COCKPIT_TABLES_DB_USER', $db_config['user']);
define('COCKPIT_TABLES_DB_PASS', $db_config['password']);
define('COCKPIT_TABLES_DB_PREF', $db_config['prefix']);


include_once(__DIR__.'/Helpers/Database.php');

try {
    // to do: better error handling to avoid this extra object
    // to do: check, if the connection is actually closed after nulling $DB
    $DB = new \Tables\Helpers\Database([\PDO::ATTR_EMULATE_PREPARES   => false]);
    $DB->instance();
    $DB = null;
}
catch(\PDOException $e) {
    // connection failed
    define('COCKPIT_TABLES_CONNECTED', false);
}
if(!defined('COCKPIT_TABLES_CONNECTED'))
    define('COCKPIT_TABLES_CONNECTED', true);

// load the whole tables bootstrap file, if connected to database
if (COCKPIT_TABLES_CONNECTED) {
    include_once(__DIR__.'/tables.php');
}

// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_API_REQUEST) {
    include_once(__DIR__.'/admin.php');
}
