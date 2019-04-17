<?php

namespace Tables\Controller;

class Settings extends \Cockpit\AuthController {

    public function index() {

        $tables = $this->app->module('tables')->getTablesInGroup(null, true);

        $origTables = $this->module('tables')->listTables();
        
        return $this->render('tables:views/settings.php', compact('tables', 'origTables'));
    }

}
