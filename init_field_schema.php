<?php
/**
 * This file is included by tables.php via the function createTableSchema(),
 * so the tables module must be extended with `$this->extend([])` instead of
 * `$this->module('tables')->extend([])`.
 */

$this->extend([

    // 'formatTableSchema' => function($schema = []) {
    'formatTableSchema' => function($schema = [], $store = true, $extended = false) {

        if (empty($schema))
            return false;

        $fields = [];
        $return_relations = [];

        $table_definitions = $schema['table'];
        $field_definitions = $schema['fields'];

        $table_name = $table_definitions['TABLE_NAME'];
        $table_type = $table_definitions['TABLE_TYPE'] == 'VIEW' ? 'view' : 'table';
        $table_group = $table_definitions['TABLE_TYPE'] == 'VIEW' ? 'views' : 'tables';
        $time = time();

        $primary_key = null;
        $database_fields = [];

        $relation_field_count = 0;
        $relation_field_count_one_to_many = 0;
        $relation_field_count_many_to_many = 0;

        foreach ($field_definitions as $column) {

            $column_name = $column['COLUMN_NAME'];

            $database_fields[] = $column_name;

            $data_type = $column['DATA_TYPE'];

            $options = [];
            $relations = [];
            $type = '';

            if ($column['COLUMN_KEY'] == "PRI")
                $primary_key = $column_name;

            $relations = $this->hasRelations($table_name, $column_name);

            if (!empty($relations) && isset($relations['references'])) {

                $type = 'relation';
                $options = [
                    'value' => $relations['references']['field'],
                    'type'  => 'one-to-many',
                    'display' => [
                        'type' => 'select',
                        'label' => $relations['references']['display_field'],
                    ],
                    'source' => [
                        'module' => 'tables',
                        'table' => $relations['references']['table'],
                        'identifier' => $relations['references']['field'],
                        'display_field' => $relations['references']['display_field'],
                    ],
                ];

                $label[$column_name] = " --> " .  $relations['references']['table'] . ' (' . $relations['references']['display_field'] . ')';

                $relation_field_count++;
                $relation_field_count_one_to_many++;

            }

            elseif (!empty($relations) && isset($relations['is_referenced_by'])) {

                $extra_fields = [];
                foreach ($relations['is_referenced_by'] as $rel) {

                    // check for foreign key relations, create extra fields of type relation

                    $referenced_table = $this->table($rel['table']);

                    $related_column_count = count($referenced_table['database_schema']['columns']);
                    $related_key_count = 0;

                    foreach ($referenced_table['database_schema']['columns'] as $related_column) {

                        $related_relations = $this->hasRelations($referenced_table['name'], $related_column);

                        if (empty($related_relations['references'])) {
                            continue;
                        }

                        $related_key_count++;

                    }

                    if ($related_key_count <= 1
                        || $related_key_count > 3
                        ) {
                            continue;
                        }

                    $related = null;
                    foreach($referenced_table['fields'] as $field) {

                        $ref = $this->hasRelations($referenced_table['name'], $field['name']);

                        if (empty($ref['references'])) {
                            continue;
                        } else {
                            $ref = $ref['references'];
                        }

                        if ($ref['table'] != $table_name) {

                            $related_table = $ref['table'];
                            $related = $ref;

                        }

                        $referenced_fields[$ref['table']] = [
                            'table' => $ref['table'],
                            'field' => $ref['field'],
                            'related_identifier' => $field['name'],
                            'display_field' => $ref['display_field'],
                        ];

                    }

                    $extra_fields[] = [
                        'name' => $rel['table'] . '_' . $rel['field'],
                        'label' => " <--> " . $related['table'] . " (" . $related['display_field'] . ")",
                        'default' => '',
                        'info' => '',
                        'required' => false,
                        'group' => '',
                        'localize' => false,
                        'type' => 'relation',
                        'width' => '1-1',
                        'lst' => true,
                        'acl' => array (),
                        'options' => [
                            'value' => $related['field'],
                            'type' => 'many-to-many',
                            'multiple' => true,
                            'separator' => ',',
                            'display' => [
                                'type' => 'select',
                                'label' => $related['display_field'],
                            ],
                            'source' => [
                                'module' => 'tables',
                                'table' => $related['table'],
                                'identifier' => $related['field'],
                                'display_field' => $related['display_field'],
                            ],
                            'target' => [
                                'module' => 'tables',
                                'table' => $rel['table'],
                                'identifier' => $rel['field'],
                                'related_identifier' => $referenced_fields[$related['table']]['related_identifier'] ?? null,
                                'display_field' => $rel['display_field'],
                            ],
                        ],
                    ];

                }

                $relation_field_count++;
                $relation_field_count_many_to_many++;

            }

            if(empty($type)) {

                if ($data_type == 'text' || ($data_type == 'varchar' && $column['CHARACTER_MAXIMUM_LENGTH'] > 100)) {
                    $type = 'textarea';
                    $options['rows'] = $data_type == 'text' ? 5 : 3;
                    if ($column['CHARACTER_MAXIMUM_LENGTH'])
                        $options['maxlength'] = $column['CHARACTER_MAXIMUM_LENGTH'];
                }

                elseif ($data_type == 'tinyint') {
                    $type = 'boolean';
                    $options['default'] = false;
                }

                elseif ($data_type == 'date')
                    $type = 'date';

                elseif ($data_type == 'int') {
                    $type = 'text';
                    $options['type'] = 'number';
                }

                else {
                    $type = 'text';
                    if ($column['CHARACTER_MAXIMUM_LENGTH'])
                        $options['maxlength'] = $column['CHARACTER_MAXIMUM_LENGTH'];
                }

            }

            if ($relations) {
                if ($store) {
                    $this->storeRelations($table_name, $column_name, $relations);
                }

                $return_relations[$table_name][$column_name] = $relations;
            }
            
            

            $fields[] = [
                'name' => $column_name,
                'label' => $label[$column_name] ?? '',
                'type' => $type,
                'default' => '',
                'info' => $column['COLUMN_COMMENT'],
                'required' => $column['IS_NULLABLE'] == 'YES' ? false : true,
                'group' => '',
                'localize' => false,
                'options' => $options,
                'width' => '1-1',
                'lst' => true,
                'acl' => array (),
            ];

        } // end of foreach $field_definitions

        // add many-to-many related extra fields 
        if (!empty($extra_fields))
            foreach ($extra_fields as $field)
                $fields[] = $field;

        // define table type for helper tables (for grouping in UI)
        // has some false positives --> easily adjusted by hand
        $count = count($fields);
        $is_many_to_many_helper = false;

        if ($count - $relation_field_count <= 2) {
            $table_group = 'z_helpers'; // table groups are sorted alphabetically
        }

        if ($count == 3 
            && $count - $relation_field_count_one_to_many <= 2
            && $relation_field_count_many_to_many == 0
            ) {
            $table_group = 'z_helpers_m:n';
            $is_many_to_many_helper = true;
        }
        // if ($count == 2 && $count - $relation_field_count_one_to_many <= 2) {
            // $table_group = 'z_helpers_1:m';
        // }

        $table = [
            'name'      => $table_name,
            'label'     => '',
            'color' => '',
            'description' => $table_definitions['TABLE_COMMENT'],
            'type' => $table_type,
            'group' => $table_group,
            'auto_delete_by_reference' => $is_many_to_many_helper,
            '_id'       => $table_name,
            'primary_key' => $primary_key,
            'fields'    => $fields,
            'sortable'  => false,
            'in_menu'   => false,
            'acl' => [],
            '_created'  => strtotime($table_definitions['CREATE_TIME']),
            '_modified' => $time,
            'database_schema' => [
                'columns' => $database_fields,
                'engine' => $table_definitions['ENGINE'],
                'charset' => $table_definitions['TABLE_COLLATION'],
                'database' => $table_definitions['TABLE_SCHEMA'],
            ],
        ];

        // return $table;
        return $extended ? ['data' => $table, 'relations' => $return_relations] : $table;

    }, // end of formatTableSchema()

    'hasRelations' => function($table = '', $field = '') {

        $relations = $this->listRelations();

        if (!$relations) return false;

        $references = [];

        foreach ($relations as $rel) {

            if ($rel['TABLE_NAME'] == $table && $rel['COLUMN_NAME'] == $field) {

                // field/column is a foreign key

                $parts[] = "SELECT COLUMN_NAME";
                $parts[] = "FROM INFORMATION_SCHEMA.COLUMNS";
                $parts[] = "WHERE TABLE_SCHEMA = :database";
                $parts[] = "AND TABLE_NAME = :table";
                // $parts[] = "AND DATA_TYPE = 'varchar'";
                $parts[] = "AND ( DATA_TYPE = 'varchar'";
                $parts[] = "OR EXTRA != 'auto_increment' )";
                $parts[] = "LIMIT 1";
                $query = implode(' ', $parts);
                $params = [
                    ':database' => $this->dbname,
                    ':table'    => $rel['REFERENCED_TABLE_NAME'],
                ];

                $display_field = $this('db')->run($query, $params)->fetch(\PDO::FETCH_ASSOC);

                $display_field = !empty($display_field['COLUMN_NAME']) ? $display_field['COLUMN_NAME'] : $rel['COLUMN_NAME'];

                $references['references'] = [
                    'table' => $rel['REFERENCED_TABLE_NAME'],
                    'field' => $rel['REFERENCED_COLUMN_NAME'],
                    'display_field' => $display_field,
                ];

            }

            unset($parts);
            unset($query);
            unset($params);

            if ($rel['REFERENCED_TABLE_NAME'] == $table && $rel['REFERENCED_COLUMN_NAME'] == $field) {

                // field/column is referenced by another foreign key

                $parts[] = "SELECT COLUMN_NAME";
                $parts[] = "FROM INFORMATION_SCHEMA.COLUMNS";
                $parts[] = "WHERE TABLE_SCHEMA = :database";
                $parts[] = "AND TABLE_NAME = :table";
                $parts[] = "AND ( DATA_TYPE = 'varchar'";
                $parts[] = "OR EXTRA != 'auto_increment' )";
                $parts[] = "LIMIT 1";
                $query = implode(' ', $parts);
                $params = [
                    ':database' => $this->dbname,
                    ':table'    => $rel['REFERENCED_TABLE_NAME'],
                ];

                $display_field = $this('db')->run($query, $params)->fetch(\PDO::FETCH_ASSOC);

                $display_field = !empty($display_field['COLUMN_NAME']) ? $display_field['COLUMN_NAME'] : $rel['REFERENCED_COLUMN_NAME'];

                $references['is_referenced_by'][] = [
                    'table' => $rel['TABLE_NAME'],
                    'field' => $rel['COLUMN_NAME'],
                    'display_field' => $display_field,
                ];

            }

        }

        return $references;

    }, // end of hasRelations()

    'getTableSchema' => function($table = null/*, $columns = '*'*/) {

        if (!$table) return false;

        $prefix   = $this->prefix;
        $database = $this->dbname;

        // $columns = is_array($columns) ? $columns : array_map('sqlIdentQuote', explode(',', $columns));

        // get field definitions
        $parts[] = "SELECT";
        // $parts[] = implode(', ',$columns);
        $parts[] = "*";
        $parts[] = "FROM `INFORMATION_SCHEMA`.`COLUMNS`";
        $parts[] = "WHERE `TABLE_SCHEMA` = :database";
        $parts[] = "AND `TABLE_NAME` LIKE :table";

        $query = implode(' ', $parts);

        $params = [
            ':database' => $database,
            ':table' => $prefix.$table,
        ];

        $stmt = $this('db')->run($query, $params);
        $field_definitions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        unset($query);
        unset($parts);

        // get table definitions
        $parts[] = "SELECT";
        $parts[] = "*";
        $parts[] = "FROM `information_schema`.`TABLES`";
        $parts[] = "WHERE `TABLE_SCHEMA` = :database";
        $parts[] = "AND TABLE_NAME = :table";

        $query = implode(' ', $parts);

        $params = [
            ':database' => $database,
            ':table' => $prefix.$table,
        ];

        $stmt = $this('db')->run($query, $params);

        $table_definitions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($table_definitions)) return false;

        $table_definitions = $table_definitions[0];

        return ['table' => $table_definitions, 'fields' => $field_definitions];

    }, // end of getTableSchema()

    'listRelations' => function($table = null, $column = null) {

        $parts[] = "SELECT";
        $parts[] = "`TABLE_NAME`";
        $parts[] = ",`COLUMN_NAME`";
        $parts[] = ",`REFERENCED_TABLE_NAME`";
        $parts[] = ",`REFERENCED_COLUMN_NAME`";
        $parts[] = "FROM `information_schema`.`key_column_usage`";
        $parts[] = "WHERE";
        $parts[] = "`REFERENCED_TABLE_NAME` IS NOT NULL";

        $parts[] = "AND `table_schema` = :database";
        $params[':database'] = $this->dbname;

        if ($table) {
            $parts[] = "AND `TABLE_NAME` = :table";
            $params[':table'] = $table;
        }

        if ($column) {
            $parts[] = "AND `COLUMN_NAME` = :column";
            $params[':column'] = $column;
        }

        $query = implode(' ', $parts);

        return $this('db')->run($query, $params)->fetchAll(\PDO::FETCH_ASSOC);

    }, // end of listRelations()

    'storeRelations' => function($table_name, $column_name, $relations = []) {

        $_relations = [];

        $relationpath = $this->app->path("#storage:tables/{$this->dbname}/relations.php");
        if (file_exists($relationpath)) {
            $_relations = include($relationpath);
        }

        $rel[$table_name][$column_name] = $relations;

        foreach ($relations as $key => $val) {
            if (!isset($_relations[$table_name][$column_name][$key])) {
                $_relations[$table_name][$column_name][$key] = $val;
            }
        }

        $export = var_export($_relations, true);

        $this->app->helper('fs')->write("#storage:tables/{$this->dbname}/relations.php", "<?php\n return {$export};");

    }, // end of storeRelations()

]);
