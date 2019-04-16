<?php

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

    // bind admin routes /tables/*
    $this->bindClass('Tables\\Controller\\Admin', 'tables');

    // add relation field to assets
    $this->helper('admin')->addAssets('tables:assets/field-relation.tag');
    $this->helper('admin')->addAssets('tables:assets/table-lockstatus.tag');

    // dashboard widgets
    $this->on("admin.dashboard.widgets", function($widgets) {

        $tables = $this->module('tables')->getTablesInGroup(null, false);

        // sort tables by group
        usort($tables, function($a, $b) {return $a['group'] <=> $b['group'];});

        $widgets[] = [
            'name'    => 'tables',
            'content' => $this->view('tables:views/widgets/dashboard.php', compact('tables')),
            'area'    => 'aside-left'
        ];

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

});
