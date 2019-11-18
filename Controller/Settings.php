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

        $missingRelations = [];
        $wrongRelations   = [];

        foreach ($relations as $table => $fields) {

            if (!isset($storedRelations[$table])) {
                $missingRelations[$table] = $fields;
                continue;
            }

            foreach ($fields as $name => $references) {

                foreach ($references as $ref_type => $refs) {
                    if (!isset($storedRelations[$table][$name][$ref_type])) {
                        $missingRelations[$table][$name][$ref_type] = $refs;
                        continue;
                    }
                    else {
                        foreach ($refs as $ref) {
                            if (!in_array($ref, $storedRelations[$table][$name][$ref_type])) {
                                $missingRelations[$table][$name][$ref_type][] = $ref;
                            }
                        }
                    }
                }
            }
        }

        foreach ($storedRelations as $table => $fields) {

            if (!isset($relations[$table])) {
                $wrongRelations[$table] = $fields;
                continue;
            }

            foreach ($fields as $name => $references) {

                foreach ($references as $ref_type => $refs) {
                    if (!isset($relations[$table][$name][$ref_type])) {
                        $wrongRelations[$table][$name][$ref_type] = $refs;
                        continue;
                    }
                    else {
                        foreach ($refs as $ref) {
                            if (!in_array($ref, $relations[$table][$name][$ref_type])) {
                                $wrongRelations[$table][$name][$ref_type][] = $ref;
                            }
                        }
                    }
                }
            }
        }

        // acl
        $_acl_groups = $this->invoke('Tables\\Controller\\Acl', 'getGroups', [true]);

        $acl_groups  = $_acl_groups['acl_groups'];
        $hardcoded   = isset($_acl_groups['hardcoded']) ? $_acl_groups['hardcoded'] : [];

        $acls = $this->app->helpers['acl']->getResources()['tables'];

        return $this->render('tables:views/settings.php', compact(
            'tables',
            'origTables',
            'acl_groups',
            'acls',
            'hardcoded',
            'missingRelations',
            'wrongRelations'
        ));

    } // end of index()

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
