<?php
/**
 * SQL table manager for Cockpit CMS
 * 
 * @see       https://github.com/raffaelj/cockpit_Tables/
 * @see       https://github.com/agentejo/cockpit/
 * 
 * @version   0.3.1
 * @author    Raffael Jesche
 * @license   MIT
 * @note      work in progress
 */

// Autoload from lib folder (PSR-0)
spl_autoload_register(function($class){
    $class_path = __DIR__.'/lib/'.str_replace('\\', '/', $class).'.php';
    if(file_exists($class_path)) include_once($class_path);
});

include_once(__DIR__.'/Helper/Database.php'); // because auto-load not ready yet

// load database config
$config = $this->retrieve('tables/db', []);

if (is_string($config) && file_exists($config)) {

    $ext = pathinfo($config, PATHINFO_EXTENSION);
    switch($ext) {
        case 'php':   $config = include($config);         break;
        case 'ini':   $config = parse_ini_file($config);  break;
        case 'yaml':  $config = Spyc::YAMLLoad($config);  break;
        default:      $config = [];
    }

}

// merge with default values
$config = array_merge([
    'host'     => 'localhost',
    'port'     => 3306,
    'dbname'   => '',
    'user'     => 'root',
    'password' => '',
    'prefix'   => '',
    'charset'  => 'utf8'
], $config);

// PDO options
$dsn = \vsprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', [
    $config['host'],
    $config['port'],
    $config['dbname'],
    $config['charset']
]);


$options = [
    // \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
    \PDO::ATTR_EMULATE_PREPARES => true, // enable to reuse params multiple times
];

// don't break cockpit if database credentials are wrong
try {
    $this->helpers['db'] = new \Tables\Helper\Database($dsn, $config['user'], $config['password'], $options);
}
catch(\PDOException $e) { // connection failed
    define('COCKPIT_TABLES_CONNECTED', false);
}

if(!defined('COCKPIT_TABLES_CONNECTED')) {
    define('COCKPIT_TABLES_CONNECTED', true);
}

$this->module('tables')->extend([

    'host'    => $config['host'],
    'port'    => $config['port'],
    'dbname'  => $config['dbname'],
    'prefix'  => $config['prefix'],

]);

if (COCKPIT_TABLES_CONNECTED) {
    include_once(__DIR__.'/tables.php');
}

// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_API_REQUEST) {
    include_once(__DIR__.'/admin.php');
}

// CLI
if (COCKPIT_CLI) {
    $this->path('#cli', __DIR__ . '/cli');
}
