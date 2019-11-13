<?php

namespace Tables\Controller;

class Settings extends \Cockpit\AuthController {

    public function index() {

        // tables
        $tables = $this->app->module('tables')->tables();

        $origTables = $this->app->module('tables')->listTables();

        // original relations
        $relations = $this->app->module('tables')->getDatabaseRelations();

        // stored relations
        $storedRelations = $this->app->module('tables')->getStoredRelations();

        ksort($relations);
        ksort($storedRelations);

        $relationsDiff = array_udiff($storedRelations, $relations, function($a, $b) {
            $a = json_encode($a);
            $b = json_encode($b);
            return $a == $b ? 0 : ($a > $b ? 1 : -1);
        });

        // acl
        $_acl_groups = $this->invoke('Tables\\Controller\\Acl', 'getGroups', [true]);

        $acl_groups = $_acl_groups['acl_groups'];
        $hardcoded = isset($_acl_groups['hardcoded']) ? $_acl_groups['hardcoded'] : [];

        $acls = $this->app->helpers['acl']->getResources()['tables'];

        // return $this->render('tables:views/settings.php', compact('tables', 'origTables', 'acl_groups', 'acls', 'hardcoded'));

        return $this->render('tables:views/settings.php', compact('tables', 'origTables', 'acl_groups', 'acls', 'hardcoded', 'relations', 'storedRelations', 'relationsDiff'));

    }

    public function saveAcl() {

        return $this->invoke('Tables\\Controller\\Acl', 'saveAcl');

    }

    public function fixWrongRelations() {

        return $this->app->module('tables')->fixWrongRelations();

    }

    public function listTables() {

        return $this->app->module('tables')->listTables();

    }

    public function getTables() {

        return $this->app->module('tables')->tables();

    }

    public function removeMissingTables() {

        $storedTables = $this->app->module('tables')->tables();
        $tables       = $this->app->module('tables')->listTables();

        $ret = [];
        $error = [];

        foreach ($storedTables as $table) {

            if (!in_array($table['name'], $tables)) {
                if ($this->app->module('tables')->removeTableSchema($table['name'])) {
                    $ret[] = $table['name'];
                } else {
                    $error[] = $table['name'];
                }
            }
        }

        return empty($error) ? ['message' => 'Removed ' . implode(', ', $ret)] : ['error' => 'Removing failed for ' . implode(', ', $error)];

    }

}
