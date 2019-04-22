<?php

namespace Tables\Controller;

class Settings extends \Cockpit\AuthController {

    public function index() {

        // tables
        $tables = $this->app->module('tables')->getTablesInGroup(null, true);

        $origTables = $this->module('tables')->listTables();

        // acl
        $_acl_groups = $this->invoke('Tables\\Controller\\Acl', 'getGroups', [true]);

        $acl_groups = $_acl_groups['acl_groups'];
        $hardcoded = $_acl_groups['hardcoded'];

        $acls = $this->app->helpers['acl']->getResources()['tables'];

        return $this->render('tables:views/settings.php', compact('tables', 'origTables', 'acl_groups', 'acls', 'hardcoded'));

    }

    public function saveAcl() {

        return $this->invoke('Tables\\Controller\\Acl', 'saveAcl');

    }

}
