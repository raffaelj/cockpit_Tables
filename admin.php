<?php

include(__DIR__.'/lib/vendor/autoload.php');

$app->on('admin.init', function() {

    if (!$this->module('cockpit')->getGroupRights('tables') && !$this->module('tables')->getTablesInGroup()) {

        $this->bind('/tables/*', function() {
            return $this('admin')->denyRequest();
        });

        return;
    }

    // add to modules menu
    $this->helper('admin')->addMenuItem('modules', [
        'label' => 'Tables',
        'icon'  => 'tables:icon.svg',
        'route' => '/tables',
        'active' => strpos($this['route'], '/tables') === 0
    ]);

    if (!COCKPIT_TABLES_CONNECTED) {

        $this->bind('/tables/*', function(){
            return $this->invoke('Tables\\Controller\\Admin', 'not_connected');
        });

        return;

    }

    // bind routes for spreadsheet export
    $this->bind('/tables/export/:table', function($param) {
        return $this->invoke('Tables\\Controller\\Export', 'export', $param);
    });

    // bind admin routes /tables/*
    $this->bindClass('Tables\\Controller\\Admin', 'tables');

    // add relation field to assets
    $this->helper('admin')->addAssets('tables:assets/field-relation.tag');
    $this->helper('admin')->addAssets('tables:assets/table-lockstatus.tag');

    // dashboard widgets
    $this->on("admin.dashboard.widgets", function($widgets) {

        $tables = $this->module('tables')->getTablesInGroup(null, false);

        // create a widget per group
        $groups = [];
        foreach($tables as $table) {
            if (isset($table['group'])) $groups[$table['group']][] = $table;
            else $groups['no group'][] = $table;
        }

        foreach($groups as $name => $group) {
            $widgets[] = [
                'name'    => 'tables_' . urlencode($name),
                'content' => $this->view('tables:views/widgets/dashboard.php', ['tables' => $group]),
                'area'    => 'aside-left'
            ];
        }

    }, 100);

    // display in aside menu
    $this->on('cockpit.menu.aside', function() {

        $cols   = $this->module('tables')->getTablesInGroup();
        $tables = [];

        if ($cols) {
            foreach($cols as $table) {
                if ($table['in_menu']) $tables[] = $table;
            }
        }

        if (count($tables)) {
            $this->renderView("tables:views/partials/menu.php", compact('tables'));
        }
    });

    if ($this->module('cockpit')->hasaccess('tables', 'manage')) {

        // settings item and page
        $this->on('cockpit.view.settings.item', function() {
            $this->renderView("tables:views/partials/settings.php");
        });
        $this->bindClass('Tables\\Controller\\Settings', 'settings/tables');

    }

});
