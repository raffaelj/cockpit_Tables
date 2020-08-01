<?php

namespace Tables\Controller;

class Acl extends \Cockpit\AuthController {

    public function index() {}

    public function getGroups($compareWithConfig = false) {

        $_acl_groups = $this->app->helpers['acl']->getGroups();

        unset($_acl_groups['admin']);
        $_acl_groups = array_keys($_acl_groups);

        $rights     = $this->app->retrieve('groups', []);
        $_hardcoded = $this->app->retrieve('config/groups', []);

        $acl_groups = [];
        $hardcoded  = [];
        foreach($_acl_groups as $group) {
            $acl_groups[$group] = $rights[$group]['tables'] ?? [];
            $hardcoded[$group]  = $_hardcoded[$group]['tables'] ?? [];
        }

        return $compareWithConfig ? compact('acl_groups', 'hardcoded') : $acl_groups;

    }

    public function saveAcl() {

        $acl = $this->app->param('acl', null);

        if (!$acl) return false;

        foreach ($acl as $group => $list) {

            $l = [
                'group' => $group,
                'tables' => $list,
            ];
            if ($e = $this->storage->findOne('tables/acl', ['group' => $group])) {
                $l['_id'] = $e['_id'];
            }

            $this->storage->save('tables/acl', $l);

        }

        return $acl;

    }

}
