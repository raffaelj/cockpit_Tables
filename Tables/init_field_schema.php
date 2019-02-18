<?php

// to do: rewrite to new pdo db class

$this->module('tables')->extend([

    'formatTableSchema' => function($schema = []) {

        if (empty($schema))
            return false;

        $fields = [];

        $table_definitions = $schema['table'];
        $field_definitions = $schema['fields'];

        $table_name = $table_definitions['TABLE_NAME'];
        $table_type = $table_definitions['TABLE_TYPE'] == 'VIEW' ? 'view' : 'table';
        $time = time();

        $primary_key = null;
        $database_fields = [];

        foreach ($field_definitions as $column) {

            $column_name = $column['COLUMN_NAME'];

            $database_fields[] = $column_name;

            $data_type = $column['DATA_TYPE'];

            $options = [];
            $relations = [];
            $type = '';

            // if ($column['COLUMN_KEY'] == "PRI" && $column['EXTRA'] == "auto_increment")
            if ($column['COLUMN_KEY'] == "PRI")
                $primary_key = $column_name;

            $relations = $this->hasRelations($table_name, $column_name);

            if (!empty($relations) && isset($relations['references'])) {

                $type = 'relation';
                $options = [
                    'request' => '/tables/find/',
                    'key' => 'entries',
                    'options' => [
                        'table' => $relations['references']['table'],
                        'options' => [
                            'fields' => [
                                $relations['references']['field'] => 1,
                                $relations['references']['display_field'] => 1,
                            ]
                        ]
                    ],
                    'value' => $relations['references']['field'],
                    'label' => $relations['references']['display_field'],
                    'source' => [
                        'module' => 'tables',
                        'table' => $relations['references']['table'],
                        'identifier' => $relations['references']['field'],
                        'display_field' => $relations['references']['display_field'],
                    ],
                ];
                
                $label[$column_name] = " --> " .  $relations['references']['table'] . ' (' . $relations['references']['display_field'] . ')';

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

debug([
  'origin' => $table_name,
  'referenced_table' => $referenced_table['name'],
  'related_column_count' => $related_column_count,
  'related_key_count' => $related_key_count
]);

                    if ($related_key_count <= 1
                        // || $related_key_count > 2
                        || $related_key_count > 3
                        // && $related_column_count - $related_key_count < 4
                        ) {
                            continue;
                        }

                    $related = null;
                    foreach($referenced_table['fields'] as $field) {

                        if (empty($field['options']['relations']['references']))
                            continue;

                        if ($field['options']['relations']['references']['table'] != $table_name) {
                            
                            $related_table = $field['options']['relations']['references']['table'];
                            $related = $field['options']['relations']['references'];
                            $test[] = $field['options']['relations']['references']['table'];
                            
                        }

                        $referenced_fields[$field['options']['relations']['references']['table']] = [
                            'table' => $field['options']['relations']['references']['table'],
                            'field' => $field['options']['relations']['references']['field'],
                            'related_identifier' => $field['name'],
                            'display_field' => $field['options']['relations']['references']['display_field'],
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
                            'request' => '/tables/find/',
                            'key' => 'entries',
                            'options' => [
                                'table' => $related['table'],
                                'options' => [
                                    'fields' => [
                                        $related['field'] => 1,
                                        $related['display_field'] => 1,
                                    ]
                                ]
                            ],
                            'value' => $related['field'],
                            'label' => $related['display_field'],
                            'referenced_table' => $rel['table'],
                            'referenced_key' => $rel['field'],
                            'multiple' => true,
                            'separator' => ',',
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

            }

            if(empty($type)) {

                if ($data_type == 'text' || ($data_type == 'varchar' && $column['CHARACTER_MAXIMUM_LENGTH'] > 100)) {
                    $type = 'textarea';
                    $options['rows'] = $data_type == 'text' ? 5 : 3;
                }

                elseif ($data_type == 'tinyint')
                    $type = 'boolean';

                elseif ($data_type == 'date')
                    $type = 'date';

                elseif ($data_type == 'int') {
                    $type = 'text';
                    $options['type'] = 'number';
                }

                else {
                    $type = 'text';
                }

            }

            if ($relations) $options['relations'] = $relations;
            // if ($validations) $options['validations'] = []; // to do

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
        }

        // add many-to-many related extra fields 
        if (!empty($extra_fields))
            foreach ($extra_fields as $field)
                $fields[] = $field;

        $table = [
            'name'      => $table_name,
            'label'     => '',
            'color' => '',
            'description' => $table_definitions['TABLE_COMMENT'],
            'type' => $table_type,
            'group' => $table_type,
            '_id'       => $table_name,
            'primary_key' => $primary_key,
            'fields'    => $fields,
            'sortable'  => false,
            'in_menu'   => false,
            // 'acl' => new \ArrayObject,
            '_created'  => strtotime($table_definitions['CREATE_TIME']),
            '_modified' => $time,
            'database_schema' => [
                'columns' => $database_fields,
                'engine' => $table_definitions['ENGINE'],
                'charset' => $table_definitions['TABLE_COLLATION'],
                'database' => $table_definitions['TABLE_SCHEMA'],
            ],
        ];

        return $table;

    }, // end of formatTableSchema()

    'hasRelations' => function($table = '', $field = '') {

        $relations = $this('db')->listRelations();

        if (!$relations) return false;

        $references = [];

        foreach ($relations as $rel) {

            if ($rel['TABLE_NAME'] == $table && $rel['COLUMN_NAME'] == $field) {
                // field/column is a foreign key
                
                $parts[] = "SELECT COLUMN_NAME";
                $parts[] = "FROM INFORMATION_SCHEMA.COLUMNS";
                $parts[] = "WHERE TABLE_SCHEMA = :database";
                $parts[] = "AND TABLE_NAME = :table";
                $parts[] = "AND DATA_TYPE = 'varchar'";
                $parts[] = "LIMIT 1";
                $query = implode(' ', $parts);
                $params = [
                    ':database' => COCKPIT_TABLES_DB_NAME,
                    ':table' => $rel['REFERENCED_TABLE_NAME'],
                ];
                
                $display_field = $this('db')->run($query, $params)->fetch(\PDO::FETCH_ASSOC);
                
                $display_field = !empty($display_field['COLUMN_NAME']) ? $display_field['COLUMN_NAME'] : $rel['COLUMN_NAME'];
                
                $references['references'] = [
                    'table' => $rel['REFERENCED_TABLE_NAME'],
                    'field' => $rel['REFERENCED_COLUMN_NAME'],
                    'display_field' => $display_field,
                    // 'query' => $query,
                    // 'params' => $params,
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
                $parts[] = "AND DATA_TYPE = 'varchar'";
                $parts[] = "LIMIT 1";
                $query = implode(' ', $parts);
                $params = [
                    ':database' => COCKPIT_TABLES_DB_NAME,
                    ':table' => $rel['REFERENCED_TABLE_NAME'],
                ];
                
                $display_field = $this('db')->run($query, $params)->fetch(\PDO::FETCH_ASSOC);
                // $display_field = $this('db')->fetch($query, $params);
                
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

        // $db_config = $this->app->retrieve('tables/db');
        $prefix = COCKPIT_TABLES_DB_PREF;
        $database = COCKPIT_TABLES_DB_NAME;

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
        
        // $field_definitions = $this->fetchAll($query, $params);
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
        
        // $table_definitions = $this->fetchAll($query, $params)[0];
        $stmt = $this('db')->run($query, $params);
        $table_definitions = $stmt->fetchAll(\PDO::FETCH_ASSOC)[0];

        // return $this->fetchAll($query, $params);
        return ['table' => $table_definitions, 'fields' => $field_definitions];

    }, // end of getTableSchema()

    'listRelations' => function($table = null, $column = null) {

        $db_config = $this->app->retrieve('tables/db');
        // $prefix = $db_config['prefix'];
        $database = $db_config['database'];

        $parts[] = "SELECT";
        $parts[] = "TABLE_NAME";
        $parts[] = ",COLUMN_NAME";
        $parts[] = ",REFERENCED_TABLE_NAME";
        $parts[] = ",REFERENCED_COLUMN_NAME";
        $parts[] = "FROM information_schema.key_column_usage";
        $parts[] = "WHERE";
        $parts[] = "REFERENCED_TABLE_NAME IS NOT NULL";

        $parts[] = "AND table_schema = :database";
        $params[':database'] = $this->db_config['database'];

        if ($table) {
            $parts[] = "AND TABLE_NAME = :table";
            $params[':table'] = $table;
        }

        if ($column) {
            $parts[] = "AND COLUMN_NAME = :column";
            $params[':column'] = $column;
        }

        $query = implode(' ', $parts);

        $stmt = $this('db')->run($query, $params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);

    }, // end of listRelations()
    
]);