<?php
/**
 * SQL table manager for Cockpit CMS
 * 
 * @see       https://github.com/raffaelj/cockpit_Tables/
 * @see       https://github.com/agentejo/cockpit/
 * 
 * @version   0.2.0
 * @author    Raffael Jesche
 * @license   MIT
 * @note      work in progress
 */

// controller autoload and path short codes need a folder naming pattern
// return if addon folder has wrong name, e. g. "cockpit_AddonName"
$name = 'Tables';

if (!isset($app['modules'][strtolower($name)])) {

    // display a warning on top of admin ui
    $app->on('app.layout.contentbefore', function() use ($name) {
        echo '<p><span class="uk-badge uk-badge-warning"><i class="uk-margin-small-right uk-icon-warning"></i>' . $name . '</span> You have to rename the addon folder <code>' . basename(__DIR__) . '</code> to <code>' . $name . '</code>.</p>';
    });

    return;
}

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
      'host' => 'localhost'
    , 'dbname' => ''
    , 'user' => 'root'
    , 'password' => ''
    , 'prefix' => ''
    , 'charset' => 'utf8'
    ], $config
);

// PDO options
$options = [
    // \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
    \PDO::ATTR_EMULATE_PREPARES => true, // enable to reuse params multiple times
];

$dsn = 'mysql:host='.$config['host'].';dbname='.$config['dbname'].';charset='.$config['charset'];

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
