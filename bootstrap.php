<?php
/**
 * SQL table manager for Cockpit CMS
 * 
 * @see       https://github.com/raffaelj/cockpit_Tables/
 * @see       https://github.com/agentejo/cockpit/
 * 
 * @version   0.1.0
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
    
$app->on('admin.init', function() {

    try {
        $this->helpers['db'] = new \Tables\Helpers\Database();
    }
    catch(\PDOException $e) { // connection failed
        define('COCKPIT_TABLES_CONNECTED', false);
    }

    if(!defined('COCKPIT_TABLES_CONNECTED'))
        define('COCKPIT_TABLES_CONNECTED', true);

});

include_once(__DIR__.'/tables.php');

// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_API_REQUEST) {
    include_once(__DIR__.'/admin.php');
}
