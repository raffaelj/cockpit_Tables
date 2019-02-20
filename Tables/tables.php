<?php

$app->on('admin.init', function(){
    $this->helpers['db'] = 'Tables\\Helpers\\Database';
});

// load the database schema to field schema guessing functions only if needed
$app->on('tables.fieldschema.init', function(){

    require_once(__DIR__.'/init_field_schema.php');

});

$app->on('tables.save.after', function($name, &$entry, $isUpdate) {

    $id = $entry['id'] ?? 'n/a';

    $message = ($isUpdate ? "updated" : "created") . " row in table $name on id: $id\r\n";

    debug($message);

});

$app->on('tables.remove.before', function($name, &$criteria) {

    $id = $criteria['id'] ?? 'n/a';

    $message = "deleting row with id: $id from table $name\r\n";

    debug($message);

});

$this->module('tables')->extend([

    'tables' => function($extended = false, $type = 'table') {
        
        // to do: read schema files
        
        $table_type = $type == 'view' ? 'VIEW' : 'BASE TABLE';
        $database = $this->app->retrieve('tables/db/database');

        $parts[] = "SELECT `TABLE_NAME`";
        $parts[] = "FROM `information_schema`.`TABLES`";
        $parts[] = "WHERE `TABLE_SCHEMA` = :database";
        $parts[] = "AND `TABLE_TYPE` = :table_type";

        $query = implode(' ', $parts);

        $params = [
            ':database' => $database,
            ':table_type' => $table_type,
        ];

        $_tables = $this('db')->run($query, $params)->fetchAll(\PDO::FETCH_COLUMN);

        // temp...
        foreach ($_tables as $table) {
            $tables[$table] = [
                'name' => $table,
                'label' => $table,
            ];
        }

        return $tables;

    },

    'table' => function($name) {

        static $tables; // cache

        if (is_null($tables)) {
            $tables = [];
        }

        if (!is_string($name)) {
            return false;
        }

        if (!isset($tables[$name])) {

            $tables[$name] = false;

            if ($path = $this->exists($name)) {
                $tables[$name] = include($path);
            }
            
            else {
                // load missing module part for table schema
                $this->app->trigger('tables.fieldschema.init');

                if ($datablase_table_schema = $this->getTableSchema($name)) {
                    $tables[$name] = $this->createTableSchema($name, $datablase_table_schema, $fromDatabase = true);
                }

            }

            // elseif ($datablase_table_schema = $this->getTableSchema($name)) { // might not work, trigger tables.fieldschema.init first
                // $tables[$name] = $this->createTableSchema($name, $datablase_table_schema, $fromDatabase = true);
            // }
        }

        return $tables[$name];

    },

    'count' => function($table, $options = []) {

        $_table = $this->table($table);
        $table = $_table['_id'];

        $filtered_query = $this->filterToQuery($_table, $options);
        $query = $filtered_query['query'];
        $params = $filtered_query['params'];

        $stmt = $this('db')->run($query, $params);
        $count = $stmt->rowCount();

        return $count;

    },

    'find' => function($table, $options = []) {

        $_table = $this->table($table);

        if (!$_table) return false;

        $name  = $table; // reset table name to stored _id
        $table = $_table['_id'];

        $filtered_query = $this->filterToQuery($_table, $options);
        $query = $filtered_query['query'];
        $params = $filtered_query['params'];

        // to do: check context rules

        $this->app->trigger('tables.find.before', [$name, &$options, false]);
        $this->app->trigger("tables.find.before.{$name}", [$name, &$options, false]);

        $entries = empty($query) ? [] : $this('db')->run($query, $params)->fetchAll(\PDO::FETCH_ASSOC);

        $this->app->trigger('tables.find.after', [$name, &$entries, false]);
        $this->app->trigger("tables.find.after.{$name}", [$name, &$entries, false]);

        return $entries;

    },// end of find()

    'findOne' => function($table, $criteria = [], $projection = null, $populate = false, $fieldsFilter = []) {

        $_table = $this->table($table);

        if (!$_table) return false;

        $name       = $table;
        $options    = [
            'filter'       => $criteria,
            'fields'       => $projection,
            'populate'     => $populate,
            'fieldsFilter' => $fieldsFilter,
            'limit'        => 1
        ];

        $entries = $this->find($name, $options);

        return $entries[0] ?? null;
    },

    'exists' => function($name) {

        // check if schema file exist
        return $this->app->path("#storage:tables/{$name}.table.php");
    },

    'save' => function($table, $data, $options = []) {

        // to do:
        // * revisions
        // * context rules

        $_table = $this->table($table);

        if (!$_table) return false;

        $name       = $_table['name'];
        $data       = isset($data[0]) ? $data : [$data];
        $modified   = time();
        $primary_key = $_table['primary_key'];
        
        $tasks = null; // for many-to-many relations

        $columns = null;
        $query = null;
        $params = null;

        $_fields = array_column($_table['fields'], 'name');

        foreach ($data as &$entry) {

            $isUpdate = isset($entry[$primary_key]);

            // to do: adjust database schema to store meta data
            if (isset($entry['_created']))  unset($entry['_created']);
            if (isset($entry['_modified'])) unset($entry['_modified']);
            if (isset($entry['_by']))       unset($entry['_by']);
            if (isset($entry['_mby']))      unset($entry['_mby']);

            // cast fields
            foreach ($_table['fields'] as $field) {

                if ($field['type'] == 'relation'
                    && isset($entry[$field['name']])
                    && isset($field['options']['multiple'])
                    && $field['options']['multiple']
                    ) {

                    // many-to-many field

                    $ref_table = $field['options']['target']['table'];

                    // sloppy check, relations field always sends array
                    if (is_string($entry[$field['name']])) {

                        // entry didn't change, do nothing
                        continue;

                    }

                    if (empty($entry[$field['name']])) {

                        // rows may exist, but nothing is selected, remove all

                        $tasks[] = [
                            'task' => 'remove',
                            'table' => $ref_table,
                            'data' => [
                                $field['options']['target']['identifier'] => $entry[$primary_key],
                            ]
                        ];

                        continue;

                    }

                    // resolve many-to-many relations

                    $result_exists = [];
                    if ($isUpdate) {

                        $identifier = $field['options']['target']['identifier'];

                        $parts = [];
                        $parts[] = "SELECT * FROM " . sqlIdentQuote($ref_table);
                        $parts[] = "WHERE " . sqlIdentQuote($identifier) . " = :$primary_key";
                        $query = implode(' ', $parts);
                        $params[":$primary_key"] = $entry[$primary_key];

                        $stmt = $this('db')->run($query, $params);
                        $result_exists = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                    }

// debug($query);
// debug($params);
// debug($result_exists);

                    if (empty($result_exists)) {

                        // no rows exist, insert all entries

                        foreach ($entry[$field['name']] as $ref_field => $ref_entry) {

                            $tasks[] = [
                                'task' => 'save',
                                'table' => $ref_table,
                                'data' => [
                                    $field['options']['target']['identifier'] => $entry[$primary_key] ?? '__last_insert_id',
                                    $field['options']['target']['related_identifier'] => $ref_entry
                                ]
                            ];

                        }

                    }

                    else {

                        // some entries may have changed

                        $revert_result = array_column($result_exists, $identifier, $field['options']['target']['related_identifier']);

                        $sent_fields = $entry[$field['name']];

                        $keep_related_field = []; // helper for debugging
                        
                        $delete_related_field = [];
                        $save_related_field = $sent_fields;

                        foreach ($revert_result as $existing_entry => $i) {

                            if (in_array($existing_entry, $sent_fields)) {

                                // entry exists, do nothing

                                $keep_related_field[] = $existing_entry;

                                $key = array_search($existing_entry, $save_related_field);

                                if ($key !== false)
                                    unset($save_related_field[$key]);

                            }

                            elseif (!in_array($existing_entry, $sent_fields)) {

                                // entry exists, but wasn't sent --> delete it

                                $delete_related_field[] = $existing_entry;

                                $key = array_search($existing_entry, $save_related_field);

                                if ($key !== false)
                                    unset($save_related_field[$key]);

                            }

                        }

// debug([
    // 'keep' => $keep_related_field,
    // 'delete' => $delete_related_field,
    // 'save' => $save_related_field
// ]);

                        foreach ($delete_related_field as $ref_entry) {

                            $tasks[] = [
                                'task' => 'remove',
                                'table' => $ref_table,
                                'data' => [
                                    $field['options']['target']['identifier'] => $entry[$primary_key],
                                    $field['options']['target']['related_identifier'] => $ref_entry
                                ]
                            ];

                        }

                        foreach ($save_related_field as $ref_entry) {

                            $tasks[] = [
                                'task' => 'save',
                                'table' => $ref_table,
                                'data' => [
                                    $field['options']['target']['identifier'] => $entry[$primary_key],
                                    $field['options']['target']['related_identifier'] => $ref_entry
                                ]
                            ];

                        }

                    }

                }

                elseif ($field['type'] == 'relation'
                    && isset($entry[$field['name']])
                    && (!isset($field['options']['multiple'])
                        || !$field['options']['multiple'])
                    ) {

                    // one-to-many field

                    // cast first key if single select field contains array
                    if (is_array($entry[$field['name']]))
                        $entry[$field['name']] = $entry[$field['name']][0];

                    $columns[] = $field['name'];

                }

                elseif (isset($entry[$field['name']])) {

                    // normal fields

                    $columns[] = $field['name'];

                }

            }

            $parts = [];
            
            if (!$isUpdate) {

                // to do (eventually): insert if not exist

                if ($columns) {

                    $escaped_columns = array_map('sqlIdentQuote', $columns);

                    $parts[] = "INSERT INTO " . sqlIdentQuote($name);
                    $parts[] = "(" . implode(',', $escaped_columns) . ")";
                    $parts[] = "VALUES (:" . implode(',:', $columns) . ")";

                    $query = implode(' ', $parts);

                    $params = [];
                    foreach ($columns as $col)
                        $params[':'.$col] = $entry[$col];

                }

            }
            else { // is update

                if ($columns) {

                    $parts[] = "UPDATE " . sqlIdentQuote($name);
                    $parts[] = "SET";
                    
                    foreach ($columns as $col)
                        if ($col != $primary_key)
                            $set[] = sqlIdentQuote($col) . " = :$col";

                    $parts[] = implode(', ', $set);
                    $parts[] = "WHERE " . sqlIdentQuote($primary_key) . " = :$primary_key";

                    $query = implode(' ', $parts);

                    $params = [];
                    foreach ($columns as $col) {
                        $params[':'.$col] = $entry[$col];
                    }

                }

            }

            $this->app->trigger('tables.save.before', [$name, &$entry, $isUpdate]);
            $this->app->trigger("tables.save.before.{$name}", [$name, &$entry, $isUpdate]);

// debug($query);
// debug($params);

            $stmt = $this('db')->run($query, $params);

            if ($stmt && !$isUpdate) {

                $__last_insert_id = $this('db')->lastInsertId();

                $entry[$primary_key] = $__last_insert_id;

            }
            
            else {
                $__last_insert_id = $entry[$primary_key];
            }

            $ret = $stmt ? true : false;

            $this->app->trigger('tables.save.after', [$name, &$entry, $isUpdate]);
            $this->app->trigger("tables.save.after.{$name}", [$name, &$entry, $isUpdate]);

// debug($tasks);

            // run tasks (save and remove) for referenced tables
            if ($ret && $tasks) {

                foreach ($tasks as $t) {
                    $task = $t['task'];

                    // search for string '__last_insert_id' and replace it
                    if (!$isUpdate) {
                        
                        // to do: better logic to avoid data manipulation.
                        // Theoretically it's possible, that someone really wants
                        // to insert the string '__last_insert_id'
                        foreach ($t['data'] as $key => &$val) {

                            if ($val == '__last_insert_id') {
                                $val = $__last_insert_id;
                            }

                        }

                    }

                    $task_return = $this->$task($t['table'], $t['data']);

// debug($task_return);

                }

            }

            $return[] = $ret ? $entry : false;

        }

        return count($return) == 1 ? $return[0] : $return;

    }, // end of save()

    'remove' => function($table, $criteria) {

// debug('remove function was called');

        $_table = $this->table($table);

        if (!$_table) return false;

        $name  = $table;
        $table = $_table['_id'];

        $primary_key = $_table['primary_key'];
        $_fields = $_table['fields'];
        $fields = array_column($_fields, 'name');

        // check foreign key relations
        $tasks = null;
        foreach ($_fields as $field) {

            $referenced_by = !empty($field['options']['relations']['is_referenced_by']) ? $field['options']['relations']['is_referenced_by'] : false;

            if ($referenced_by) {
                foreach ($referenced_by as $ref) {
                    $tasks[] = [
                        'table' => $ref['table'],
                        'data' => [
                            $ref['field'] => $criteria[$primary_key]
                        ],
                    ];
                }

            }

        }

        // call own remove function for referenced tables first
        if ($tasks) {

// debug($tasks);

            foreach ($tasks as $task) {

                $this->app->trigger('tables.removereference.before', [$task['table'], &$task['data']]);
                $this->app->trigger("tables.remove.removereference.{$task['table']}", [$task['table'], &$task['data']]);

                $result = $this->remove($task['table'], $task['data']);

                $this->app->trigger('tables.removereference.after', [$task['table'], $result]);
                $this->app->trigger("tables.removereference.after.{$task['table']}", [$task['table'], $result]);

            }

        }

        // to do: context rules

        // filter rules
        $filter = null;
        foreach ($criteria as $field => $value) {
            if (in_array($field, $fields))
                $filter[$field] = $value;
        }

        $query = '';
        $parts = [];
        $params = [];

        if ($filter) {
             $parts[] = "DELETE FROM " . sqlIdentQuote($table);

             $i = 0;
             foreach ($filter as $field => $value) {

                $parts[] = $i == 0 ? "WHERE" : "AND";
                $parts[] = sqlIdentQuote($field) . " = :$field";

                $params[":$field"] = $value;
                
                $i++;

             }
        }

        $query = implode(' ', $parts);

// debug($query);
// debug($params);

// return ['query' => $query, 'params' => $params];

        $this->app->trigger('tables.remove.before', [$name, &$criteria]);
        $this->app->trigger("tables.remove.before.{$name}", [$name, &$criteria]);

        // $stmt = $this('db')->run($query, $params);
        $result = $this('db')->run($query, $params);
        
        // $result = empty($query) ? [] : $this('db')->query($query, $params);

        $this->app->trigger('tables.remove.after', [$name, $result]);
        $this->app->trigger("tables.remove.after.{$name}", [$name, $result]);

        return $result;
    }, // end of remove()

    'createTableSchema' => function($name, $data = [], $fromDatabase = false) {

        if (!trim($name)) {
            return false;
        }

        if ($fromDatabase) {
            $this->app->trigger('tables.fieldschema.init');
            $data = $this->formatTableSchema($data);
        }

        $configpath = $this->app->path('#storage:').'/tables';

        if (!$this->app->path('#storage:tables')) {
            if (!$this->app->helper('fs')->mkdir($configpath)) {
                return false;
            }
        }

        $time = time();

        $table = array_replace_recursive([
            'name'      => $name,
            'label'     => '',
            '_id'       => $name,
            'fields'    => [],
            'sortable'  => false,
            'in_menu'   => false,
            '_created'  => $time,
            '_modified' => $time
        ], $data);

        $export = var_export($table, true);

        if (!$this->app->helper('fs')->write("#storage:tables/{$name}.table.php", "<?php\n return {$export};")) {
            return false;
        }

        $this->app->trigger('tables.createtableschema', [$table]);

        return $table;

    },

    'updateTableSchema' => function($name, $data = []) {

        $metapath = $this->app->path("#storage:tables/{$name}.table.php");

        if (!$metapath) {
            return false;
        }

        $data['_modified'] = time();

        $table  = include($metapath);
        $table  = array_merge($table, $data);
        $export = var_export($table, true);

        if (!$this->app->helper('fs')->write($metapath, "<?php\n return {$export};")) {
            return false;
        }

        $this->app->trigger('tables.updatetableschema', [$table]);
        $this->app->trigger("tables.updatetableschema.{$name}", [$table]);

        if (function_exists('opcache_reset')) opcache_reset(); // to do: What does this line do exactly?

        return $table;

    },

    'saveTableSchema' => function($name, $data, $rules = null) {

        if (!trim($name)) {
            return false;
        }

        // to do: context rules

        return isset($data['_id']) ? $this->updateTableSchema($name, $data) : $this->createTableSchema($name, $data);
    },
    
    'references' => function($field) {

        if (!empty($field['options']['relations']['references']))
            return $field['options']['relations']['references'];

        return false;

    },
    
    'is_referenced_by' => function($field) {

        if (!empty($field['options']['relations']['is_referenced_by']))
            return $field['options']['relations']['is_referenced_by'];

        return false;

    },

    'is_filtered_out' => function($field_name, $fieldsFilter) {

        if (!$fieldsFilter                              // select all
            || !(isset($fieldsFilter[$field_name])   // or select all fields, that
                && !$fieldsFilter[$field_name])      // aren't explicitly set to false
            ) {

            if ($fieldsFilter
                && in_array(true, $fieldsFilter)        // one filter is set to true
                && empty($fieldsFilter[$field_name]) // don't select any other fields
                || (isset($fieldsFilter[$field_name])
                    && !$fieldsFilter[$field_name])
                ) {

                return true;

            }

        }

        return false;

    },

    'filterToQuery' => function($_table, $options = []) {

        if (is_string($_table))
            $_table = $this->table($table);

        if (!$_table) return ['query' => '', 'params' => null];

        // cache - don't check all filters and relations
        // if the same query is called multiple times
        static $queries;

        if (is_null($queries)) {
            $queries = [];
        }

        $table = $_table['_id'];
        $hash = md5(json_encode([$table, $options]));
        if (isset($queries[$hash])) return $queries[$hash];

        // query variables
        $select = [];
        $joins = [];
        $group_by = '';
        $where = [];
        $order_by = [];
        $query = '';
        $params = [];

        $primary_key = $_table['primary_key'];

        // cast filter options
        $fieldsFilter = $options['fields']  ?? null; // (un)select columns

        $populate     = $options['populate']    ?? false; // auto-join

        $limit        = isset($options['limit']) ? (int)$options['limit'] : null;
        $offset       = isset($options['skip'])  ? (int)$options['skip']  : 0;

        $fulltext_search  = !empty($options['filter']) && is_string($options['filter'])
                            ? $options['filter'] : false;
        $filter           = !empty($options['filter']) && is_array($options['filter']) && !$fulltext_search
                            ? $options['filter'] : false;

        $sort = isset($options['sort']) && is_array($options['sort'])
                ? array_map(function($e){return $e == -1 ? 'DESC' : 'ASC';}, $options['sort'])
                : false;

        // cast fields

        $database_columns = $_table['database_schema']['columns'];
        $available_fields = [];
        
        foreach ($_table['fields'] as $field) {
     
            if ($field['type'] != 'relation'                      // is no relation field
                && in_array($field['name'], $database_columns)    // column exists in db table
                && !$this->is_filtered_out($field['name'], $fieldsFilter)
                ) {
                
                // normal fields, do standard logic

                $select[] = sqlIdentQuote([$table, $field['name']], $field['name']);
                $available_fields[] = ['table' => $table, 'field' => $field['name']];

            }

            else {

                // resolve related fields

                // one-to-many, no auto-join
                if (!$populate
                    && $field['type'] == 'relation'
                    && $this->references($field)
                    && !$this->is_filtered_out($field['name'], $fieldsFilter)
                    ) {
                    $select[] = sqlIdentQuote([$table, $field['name']], $field['name']);
                    $available_fields[] = ['table' => $table, 'field' => $field['name']];
                }

                // one-to-many, auto-join if populate
                if ($populate
                    && $field['type'] == 'relation'
                    && ($ref = $this->references($field))
                    && !$this->is_filtered_out($field['name'], $fieldsFilter)
                    ) {

                    $referenced_table = $ref['table'];

                    $joins[] = "LEFT OUTER JOIN " . sqlIdentQuote($referenced_table);
                    $joins[] = "ON " . sqlIdentQuote([$table, $field['name']]);
                    $joins[] = "= " . sqlIdentQuote([$referenced_table, $ref['field']]); // to do: params

                    $select[] = sqlIdentQuote([$referenced_table, $ref['display_field']], $field['name']);
                    $available_fields[] = ['table' => $referenced_table, 'field' => $ref['display_field']];

                }

                // many-to-many fields
                elseif ($field['type'] == 'relation'
                        && isset($field['options']['multiple'])
                        && $field['options']['multiple']
                        && !$this->is_filtered_out($field['name'], $fieldsFilter)
                    ) {

                    $many_to_many_table = $field['options']['target']['table'];
                    $many_to_many_table_key = $field['options']['target']['identifier'];

                    $joins[] = "LEFT OUTER JOIN " . sqlIdentQuote($many_to_many_table);
                    $joins[] = "ON " . sqlIdentQuote([$table, $primary_key]);
                    $joins[] = "= " . sqlIdentQuote([$many_to_many_table, $many_to_many_table_key]); // to do: params

                    $referenced_table = $field['options']['source']['table'];
                    $referenced_table_key = $field['options']['source']['identifier'];
                    $referenced_table_field = $field['options']['target']['related_identifier'];
                    $separator = $field['options']['separator'] ?? ',';

                    $joins[] = "LEFT OUTER JOIN " . sqlIdentQuote($referenced_table);
                    $joins[] = "ON " . sqlIdentQuote([$many_to_many_table, $many_to_many_table_key]);
                    $joins[] = "= " . sqlIdentQuote([$referenced_table, $referenced_table_key]); // to do: params

                    $select_comma_separated = sqlIdentQuote([$many_to_many_table, $referenced_table_field]);

                    $select[] = "GROUP_CONCAT(DISTINCT $select_comma_separated SEPARATOR '$separator') AS " . sqlIdentQuote($field['name']);
                    $available_fields[] = ['table' => $many_to_many_table, 'field' => $referenced_table_field];

                    if (empty($group_by)) // is always the same, don't overwrite it for all relation fields
                        $group_by = sqlIdentQuote([$table, $primary_key]);

                }

            }

        }
        
        $selectable_fields = array_column($available_fields, 'field');
        
        // where filter

        if ($fulltext_search) {   // fulltext search LIKE
            $i = 0;
            foreach ($available_fields as $field) {
                
                $where[] = $i == 0 ? "WHERE" : "OR";
                $where[] = sqlIdentQuote([$field['table'], $field['field']]) . " LIKE :fulltextsearch";
                $i++;
            }
            $params[":fulltextsearch"] = "%$fulltext_search%";
            
        }
        
        if ($filter) {            // where persons.id = 2

            $i = 0;
            foreach ($available_fields as $filter_field) {

                if (!empty($filter[$filter_field['field']])) {

                    $where[] = $i == 0 ? "WHERE" : "AND";
                    $where[] = sqlIdentQuote([$filter_field['table'], $filter_field['field']]) . " = :" . $filter_field['field'];

                    $params[":".$filter_field['field']] = $filter[$filter_field['field']];
                    $i++;
                }
            }

        }

        // order by

        if ($sort) {
            foreach ($sort as $field => $direction)
                if (in_array($field, $selectable_fields))
                    $order_by[] = sqlIdentQuote($field) . " " . $direction;
        }

        // format the query

        if (empty($select)) return [];

        $parts[] = "SELECT " . implode(', ', $select);
        $parts[] = "FROM " . sqlIdentQuote($table);

        if(!empty($joins))    $parts[] = implode(' ', $joins);
        if(!empty($where))    $parts[] = implode(' ', $where);
        if(!empty($group_by)) $parts[] = "GROUP BY $group_by";
        if(!empty($order_by)) $parts[] = "ORDER BY";
        if(!empty($order_by)) $parts[] = implode(' ', $order_by);
        if($limit)            $parts[] = "LIMIT $offset, $limit";

        $query = implode(' ', $parts);

// debug($query);
// debug($params);

        $queries[$hash] = $query;
        
        return ['query' => $query, 'params' => $params];

    }, // end of query()

]);

/***
 * input:  (string)  column_name
 * output: (string) `column_name`
 * 
 * input:  (array) ['table_name','column_name']
 * output: (string) `table_name`.`column_name`
 */
function sqlIdentQuote($identifier, $as = null) {

    $escaped = null;
    $as = $as ? " AS `$as`" : '';

    if (!is_array($identifier)) {
        $escaped = trim($identifier);
        return ($escaped == '*') ? $escaped : "`$escaped`" . ($as ?? '');
    }

    foreach ($identifier as $part) {
        $escaped[] = sqlIdentQuote($part);
    }
    
    return implode('.', $escaped) . ($as ?? '');
}

// ACL
$app('acl')->addResource('tables', ['create', 'delete', 'manage']);

$this->module('tables')->extend([

    // 'getTablesInGroup' => function($group = null, $extended = false) {
    'getTablesInGroup' => function($group = null, $extended = false, $type = 'table') {

        if (!$group) {
            $group = $this->app->module('cockpit')->getGroup();
        }

        $_tables = $this->tables($extended, $type);
        $tables = [];

        if ($this->app->module('cockpit')->isSuperAdmin()) {
            return $_tables;
        }

        foreach ($_tables as $table => $meta) {

            if (isset($meta['acl'][$group]['entries_view']) && $meta['acl'][$group]['entries_view']) {
                $tables[$table] = $meta;
            }
        }

        return $tables;
    },

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

        if (isset($table['acl'][$group][$action])) {
            return $table['acl'][$group][$action];
        }

        return false;
    }
]);
