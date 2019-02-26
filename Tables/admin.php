<?php

$app->on('admin.init', function() {

    // add relation field to assets
    $this->helper('admin')->addAssets('tables:assets/field-relation.tag');

    if (!$this->module('cockpit')->getGroupRights('tables') && !$this->module('tables')->getTablesInGroup()) {

        $this->bind('/tables/*', function() {
            return $this('admin')->denyRequest();
        });

        return;
    }

    if (COCKPIT_TABLES_CONNECTED) {

        // bind admin routes /tables/*
        $this->bindClass('Tables\\Controller\\Admin', 'tables');

        // dashboard widgets
        $this->on("admin.dashboard.widgets", function($widgets) {

            // $tables = $this->module('tables')->getTablesInGroup(null, true);
            $tables = $this->module('tables')->tables();

            $widgets[] = [
                'name'    => 'tables',
                'content' => $this->view('tables:views/widgets/dashboard.php', compact('tables')),
                'area'    => 'aside-left'
            ];

        }, 100);
    
    }

    if (!COCKPIT_TABLES_CONNECTED) {

        $this->bind('/tables/*', function(){
            return $this->invoke('Tables\\Controller\\Admin', 'not_connected');
        });

    }

    // add to modules menu
    $this->helper('admin')->addMenuItem('modules', [
        'label' => 'Tables',
        'icon'  => 'tables:icon.svg',
        'route' => '/tables',
        'active' => strpos($this['route'], '/tables') === 0
    ]);

    // display in aside menu
    $this->on('cockpit.menu.aside', function() {

        $cols   = $this->module('tables')->getTablesInGroup();
        $tables = [];

        foreach($cols as $table) {
            if ($table['in_menu']) $tables[] = $table;
        }

        if (count($tables)) {
            $this->renderView("tables:views/partials/menu.php", compact('tables'));
        }
    });

});
