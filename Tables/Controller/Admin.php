<?php

namespace Tables\Controller;

class Admin extends \Cockpit\AuthController {

    public function index() {

        $_tables = $this->module('tables')->getTablesInGroup(null, true);
        $tables  = [];

        foreach ($_tables as $table => $meta) {

            $meta['allowed'] = [
                'delete' => $this->module('cockpit')->hasaccess('tables', 'delete'),
                'create' => $this->module('cockpit')->hasaccess('tables', 'create'),
                'edit' => $this->module('tables')->hasaccess($table, 'table_edit'),
                'entries_create' => $this->module('tables')->hasaccess($table, 'table_create')
            ];

            $tables[] = [
              'name' => $table,
              'label' => isset($meta['label']) && $meta['label'] ? $meta['label'] : $table,
              'meta' => $meta
            ];
        }

        // sort tables
        usort($tables, function($a, $b) {
            return mb_strtolower($a['label']) <=> mb_strtolower($b['label']);
        });

        return $this->render('tables:views/index.php', compact('tables'));
        
        
        
/* 
        // $tables = $this->app->module('tables')->tables();
        $tables = $this->app->module('tables')->getTablesInGroup();
        
        // $views = $this->app->module('tables')->tables(false, 'view');
        // $views = $this->app->module('tables')->getTablesInGroup(null, false, 'view');

        return $this->render('tables:views/index.php', compact('tables'));
        // return $this->render('tables:views/index.php', compact('tables', 'views'));
 */
    }

    public function not_connected() {
        
        if (!COCKPIT_TABLES_CONNECTED) {
            return $this->render('tables:views/not_connected.php');
        }

    }

    public function table($name = null) {

        if ($name && !$this->module('tables')->hasaccess($name, 'table_edit')) {

            return $this->helper('admin')->denyRequest();
        }

        if (!$name && !$this->module('cockpit')->hasaccess('tables', 'create')) {
            return $this->helper('admin')->denyRequest();
        }

        if ($name) {

            $table = $this->module('tables')->table($name);

            if (!$table) {
                return false;
            }
        }

        else {
            $table = [
                'name' => '',
                'label' => '',
                'color' => '',
                'fields'=>[],
                // 'acl' => new \ArrayObject,
                'sortable' => false,
                'in_menu' => false
          ];
        }

        // acl groups
        $aclgroups = [];

        foreach ($this->app->helper('acl')->getGroups() as $group => $superAdmin) {

            if (!$superAdmin) $aclgroups[] = $group;
        }

        // to do...
        $templates = [];
        $rules = [];

        return $this->render('tables:views/table.php', compact('table', 'templates', 'aclgroups', 'rules'));
        // return $this->render('tables:views/table.php', compact('table'));

    }

    public function save_table() {

        $table = $this->param('table');
        $rules = $this->param('rules', null);

        if (!$table) {
            return false;
        }

        if (!isset($table['_id']) && !$this->module('cockpit')->hasaccess('table', 'create')) {
            return $this->helper('admin')->denyRequest();
        }

        if (isset($table['_id']) && !$this->module('tables')->hasaccess($table['name'], 'table_edit')) {
            return $this->helper('admin')->denyRequest();
        }

        return $this->module('tables')->saveTableSchema($table['name'], $table, $rules);
    }

    public function entries($table) {

        if (!$this->module('tables')->hasaccess($table, 'entries_view')) {
            return $this->helper('admin')->denyRequest();
        }

        $table = $this->module('tables')->table($table);

        if (!$table) {
            return false;
        }

        $count = $this->module('tables')->count($table['name']);

        $table = array_merge([
            'sortable' => false,
            'color' => '',
            'icon' => '',
            'description' => ''
        ], $table);

        // to do: add context rules

        $view = 'tables:views/entries.php';

        if ($override = $this->app->path('#config:tables/'.$table['name'].'/views/entries.php')) {
            $view = $override;
        }

        return $this->render($view, compact('table', 'count'));

    }

    public function find() {

        $table = $this->app->param('table');
        $options    = $this->app->param('options');

        if (!$table) return false;

        $table = $this->app->module('tables')->table($table);

        $entries = $this->app->module('tables')->find($table['name'], $options);

        $count   = $this->app->module('tables')->count($table['name'], isset($options['filter']) ? ['filter' => $options['filter']] : []);

        $pages   = isset($options['limit']) ? ceil($count / $options['limit']) : 1;
        $page    = 1;

        if ($pages > 1 && isset($options['skip'])) {
            $page = ceil($options['skip'] / $options['limit']) + 1;
        }

        return compact('entries', 'count', 'pages', 'page');

    }

    public function entry($table, $id = null) {

        if ($id && !$this->module('tables')->hasaccess($table, 'entries_view')) {
            return $this->helper('admin')->denyRequest();
        }

        if (!$id && !$this->module('tables')->hasaccess($table, 'entries_create')) {
            return $this->helper('admin')->denyRequest();
        }

        $table    = $this->module('tables')->table($table);
        $primary_key = $table['primary_key'];
        $entry         = new \ArrayObject([]);
        $excludeFields = [];

        if (!$table) {
            return false;
        }

        $table = array_merge([
            'sortable' => false,
            'color' => '',
            'icon' => '',
            'description' => ''
        ], $table);
        
        // dirty test                           <--------------------------- !!!
        // $table['fields'][0]['name'] = '_id';
        // unset($table['fields'][0]); // hide id field from form
        array_splice($table['fields'],0,1); // hide id field from form

        if ($id) {

            // $entry = $this->module('tables')->findOne($table['name'], ['_id' => $id]);
            $entry = $this->module('tables')->findOne($table['name'], [$primary_key => $id]);

            if (!$entry) {
                return false;
            }
        }

        // to do: context rules

        $view = 'tables:views/entry.php';

        if ($override = $this->app->path('#config:tables/'.$table['name'].'/views/entry.php')) {
            $view = $override;
        }

        $excludeFields = []; // dirty           <--------------------------- !!!
        // why the heck do we need this?

        return $this->render($view, compact('table', 'entry', 'excludeFields'));
        // return $this->render($view, compact('table', 'entry'));

    }

    public function save_entry($table) {

        $table = $this->module('tables')->table($table);

        if (!$table) {
            return false;
        }

        $entry = $this->param('entry', false);
// return $entry;
        if (!$entry) return false;

        if (!isset($entry['_id']) && !$this->module('tables')->hasaccess($table['name'], 'entries_create')) {
            return $this->helper('admin')->denyRequest();
        }

        if (isset($entry['_id']) && !$this->module('tables')->hasaccess($table['name'], 'entries_edit')) {
            return $this->helper('admin')->denyRequest();
        }

        // $entry['_mby'] = $this->module('cockpit')->getUser('_id');

        // if (isset($entry['_id'])) {
            // $_entry = $this->module('tables')->findOne($table['name'], ['_id' => $entry['_id']]);
            // $revision = !(json_encode($_entry) == json_encode($entry));
        // } else {

            // $entry['_by'] = $entry['_mby'];
            // $revision = true;
            $revision = false;

            // if ($collection['sortable']) {
                 // $entry['_o'] = $this->app->storage->count("collections/{$collection['_id']}", ['_pid' => ['$exists' => false]]);
            // }
        // }

        $entry = $this->module('tables')->save($table['name'], $entry, ['revision' => $revision]);

        return $entry;
    }

    public function delete_entries($table) {

        $table = $this->module('tables')->table($table);

        if (!$table) {
            return false;
        }

        if (!$this->module('tables')->hasaccess($table['name'], 'entries_delete')) {
            return $this->helper('admin')->denyRequest();
        }

        $filter = $this->param('filter', false);

        if (!$filter) {
            return false;
        }

        return $this->module('tables')->remove($table['name'], $filter);
        
        // return $ret ? true : false;
        // return var_dump($ret);

        // return true;
    }

    public function init_schema($table = '') {

        // reset all stored field schemas with auto-guessed fields from database schema 

        if (!$this->app->module('cockpit')->isSuperAdmin())
            return $this->helper('admin')->denyRequest();

        $this->app->trigger('tables.fieldschema.init');
        
        if ($table == 'init_all') {

            $tables = $this('db')->listTables();

            foreach ($tables as $t) {
                $this->module('tables')->createTableSchema($t, null, true);
            }
            return ['reset' => 'all'];

        }

        return $this->module('tables')->createTableSchema($table, null, $fromDatabase = true);

    }

    public function test() {
        
        // $_test = $this->module('tables')->table('sexes');
        // return $_test['fields'];
        
        // $test = $this->module('tables')->wtf("SELECT * FROM sexes");
        
        // return var_dump($test);

    }
    
    

}
