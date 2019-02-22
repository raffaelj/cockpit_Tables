<?php

$app->on('admin.init', function() {

    // add relation field to assets
    $this->helper('admin')->addAssets('tables:assets/field-relation.tag');

    // add to modules menu
    $this->helper('admin')->addMenuItem('modules', [
        'label' => 'Tables',
        'icon'  => 'tables:icon.svg',
        'route' => '/tables',
        'active' => strpos($this['route'], '/tables') === 0
    ]);

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

});
