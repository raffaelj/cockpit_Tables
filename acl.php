<?php

$this('acl')->addResource('tables', [
    'admin',          // admin rights for tables
    'manage',         // advanced rights for tables
    'create',         // can create tables
    'delete',         // can delete tables
    'table_edit',     // global "table_edit"
    'entries_view',   // global "entries_view"
    'entries_edit',   // global "entries_edit"
    'entries_create', // global "entries_edit"
    'entries_delete', // global "entries_edit"
]);


// group acl via db
$db_groups = (array)$this->storage->find('tables/acl');
$groups    = $this->retrieve('config/groups');

foreach($db_groups as $group) {

    foreach ($group['tables'] as $action => $status) {

        if (!isset($groups[$group['group']]['tables'][$action])) {
            $groups[$group['group']]['tables'][$action] = $status;

            if ($status) {
               $this('acl')->allow($group['group'], 'tables', $action);
            }
        }
    }

}
$this->set('groups', $groups);


$this->module('tables')->extend([

    'getTablesInGroup' => function($group = null, $extended = false, $type = 'table') {

        if (!$group) {
            $group = $this->app->module('cockpit')->getGroup();
        }

        $_tables = $this->tables($extended, $type);
        $tables = [];

        if ($this->app->module('cockpit')->isSuperAdmin()) {
            return $_tables;
        }

        if ($this->app->module('cockpit')->hasaccess('tables', ['admin', 'manage', 'entries_view'], $group)) {
            return $_tables;
        }

        foreach ($_tables as $table => $meta) {

            if (isset($meta['acl'][$group]['entries_view']) && $meta['acl'][$group]['entries_view']) {
                $tables[$table] = $meta;
            }
        }

        return $tables;

    }, // end of getTablesInGroup()

    'hasaccess' => function($table, $action, $group = null) {

        $table = $this->table($table);

        if (!$table) {
            return false;
        }

        if (!$group) {
            $group = $this->app->module('cockpit')->getGroup();
        }

        if ($this->app->module('cockpit')->isSuperAdmin($group)) {
            return true;
        }

        if ($this->app->module('cockpit')->hasaccess('tables', ['admin', 'manage'], $group)) {
            return true;
        }

        // allow global rights
        foreach(['entries_view', 'entries_edit', 'entries_create', 'entries_delete'] as $a) {
            if ($action == $a && $this->app->module('cockpit')->hasaccess('tables', $a, $group)) {
                return true;
            }
        }

        if (isset($table['acl'][$group][$action])) {
            return $table['acl'][$group][$action];
        }

        return false;

    } // end of hasaccess()

]);
