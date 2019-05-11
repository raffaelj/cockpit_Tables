<?php

namespace Tables\Helper;

class Query {

    // variables for a possible multi database useage and prefixed tables in 
    // the future - not implemented yet
    protected $host   = '';
    protected $dbname = '';
    protected $prefix = '';

    // mandatory variables
    protected $_table      = [];         // table definitions
    protected $table       = '';         // table name
    protected $primary_key = '';         // column name of primary key

    // query variables
    protected $select   = [];
    protected $joins    = [];
    protected $group_by = '';
    protected $where    = [];
    protected $order_by = [];
    protected $query    = '';
    protected $params   = [];

    // cast filter options
    protected $fields          = null;  // (un)select columns
    protected $populate        = false; // auto-join
    protected $limit           = null;  // LIMIT
    protected $offset          = 0;     // OFFSET
    protected $fulltext_search = false; // WHERE foo LIKE "%bar%" AND baz LIKE "%bar%"
    protected $filter          = false; // WHERE foo = "bar"
    protected $sort            = false; // ORDER BY

    // cast fields
    protected $database_columns = [];   // check field names against column names
    protected $available_fields = [];   // for filter iterations
    protected $sortable_fields  = [];   // m:n fields can be sorted by hidden fields
    protected $normalize        = [];   // contains field names with GROUP_CONCAT strings

    public function __construct($app, $db_config) {

        $this->app = $app;

        $this->host   = $db_config['host']   ?? '';
        $this->dbname = $db_config['dbname'] ?? '';
        $this->prefix = $db_config['prefix'] ?? '';

    }

    public function init($_table, $options) {

        $this->_table = $_table;

        $this->table            = $this->_table['_id'] ?? $this->_table['name'];
        $this->primary_key      = $this->_table['primary_key'];
        $this->database_columns = $this->_table['database_schema']['columns'] ?? [];

        $this->initFilters($options);
        $this->initFields();
        $this->setWhere();
        $this->setOrderBy();
        $this->setQuery();

        return $this;

    }

    public function initFilters($options) {

        // cast filter options
        $this->fields   = $options['fields']   ?? null; // (un)select columns

        $this->populate = $options['populate'] ?? false; // auto-join

        $this->limit    = isset($options['limit']) ? (int)$options['limit'] : null;
        $this->offset   = isset($options['skip'])  ? (int)$options['skip']  : 0;

        $this->fulltext_search = !empty($options['filter'])
                && is_string($options['filter'])
                ? $options['filter'] : false;

        $this->filter   = !empty($options['filter'])
                && is_array($options['filter']) && !$this->fulltext_search
                ? $options['filter'] : false;

        $this->sort     = isset($options['sort']) && is_array($options['sort'])
                ? array_map(function($e){return $e == -1 ? 'DESC' : 'ASC';}, $options['sort'])
                : false;

    }

    public function initFields() {

        foreach ($this->_table['fields'] as $field) {

            if ($this->is_filtered_out($field['name'])) {
                continue;
            }

            // column exists in current table
            if (in_array($field['name'], $this->database_columns)){

                // normal fields, do standard logic
                if ($field['type'] != 'relation') {
                    $this->initNormalField($field);
                }

                // resolve one-to-many fields
                else {
                    $this->initOneToManyField($field);
                }

            }

            // resolve many-to-many fields
            elseif ($field['type'] == 'relation') {
                $this->initManyToManyField($field);
            }

        }

    }

    public function initNormalField($field) {

        $this->select[] = sqlIdentQuote([$this->table, $field['name']], $field['name']);
        $this->available_fields[] = ['table' => $this->table, 'field' => $field['name']];

    }

    public function initOneToManyField($field) {

        $ref = $this->getReferences($this->table, $field['name'], 'references');

        if (!$ref) return;

        // no auto-join
        if (!$this->populate) {
            $this->select[] = sqlIdentQuote([$this->table, $field['name']], $field['name']);
            $this->available_fields[] = ['table' => $this->table, 'field' => $field['name']];
        }

        // auto-join
        else {

            $referenced_table = $ref['table'];

            $this->joins[] = "LEFT OUTER JOIN " . sqlIdentQuote($referenced_table);
            $this->joins[] = "ON " . sqlIdentQuote([$this->table, $field['name']]);
            $this->joins[] = "= " . sqlIdentQuote([$referenced_table, $ref['field']]); // to do: params

            $this->select[] = sqlIdentQuote([$referenced_table, $ref['display_field']], $field['name']);
            $this->available_fields[] = ['table' => $referenced_table, 'field' => $ref['display_field']];
            $this->sortable_fields[] = ['table' => $this->table, 'field' => $field['name']];

        }

    }

    public function initManyToManyField($field) {

        // check options

        $many_to_many_table     = $field['options']['target']['table'] ?? false;
        $many_to_many_table_key = $field['options']['target']['identifier'] ?? false;

        $referenced_table       = $field['options']['source']['table'] ?? false;
        $referenced_table_key   = $field['options']['source']['identifier'] ?? false;
        $referenced_table_field = $field['options']['target']['related_identifier'] ?? false;

        // don't break the query if one of the options is not set or is empty string

        if ( empty($many_to_many_table)
          || empty($many_to_many_table_key)
          || empty($referenced_table)
          || empty($referenced_table_key)
          || empty($referenced_table_field)
        ) return;

        // build the query with two joins and a GROUP_CONCAT with separator

        $separator = $field['options']['separator'] ?? ',';

        $this->joins[] = "LEFT OUTER JOIN " . sqlIdentQuote($many_to_many_table);
        $this->joins[] = "ON " . sqlIdentQuote([$this->table, $this->primary_key]);
        $this->joins[] = "= " . sqlIdentQuote([$many_to_many_table, $many_to_many_table_key]);

        $select_comma_separated = sqlIdentQuote([$many_to_many_table, $referenced_table_field]);
        $this->available_fields[] = ['table' => $many_to_many_table, 'field' => $referenced_table_field];

        $this->select[] = "GROUP_CONCAT(DISTINCT $select_comma_separated SEPARATOR '$separator') AS " . sqlIdentQuote($field['name']);

        // GROUP_CONCAT IDs and add a key, so normalizeGroupConcat() knows, how to handle it
        // used for entries view and for export
        if ($this->populate == 2) {

            $source_table_display_field = $field['options']['source']['display_field'] ?? false;
            $source_table = $field['options']['source']['table'] ?? false;

            $this->normalize[] = [
                'field' => $field['name'],
                'separator' => $separator,
                'populate' => [
                    'table' => $source_table,
                    'field' => $source_table_display_field,
                ]
            ];

        }

        // GROUP_CONCAT IDs
        else {
            $this->normalize[] = ['field' => $field['name'], 'separator' => $separator];
        }
        
        // make m:n fields sortable
        if (isset($this->sort[$field['name']])) {

            $this->joins[] = "LEFT OUTER JOIN " . sqlIdentQuote($referenced_table);
            $this->joins[] = "ON " . sqlIdentQuote([$many_to_many_table, $referenced_table_field]);
            $this->joins[] = "= " . sqlIdentQuote([$referenced_table, $referenced_table_key]);

            // replace the virtual field name with the actual table representation
            // and keep the existing order
            $new_sort = [];
            foreach($this->sort as $key => $val) {

                if ($key == $field['name']) {

                    // sort by display_field
                    if ($this->populate == 2) {
                        $new_sort[$source_table_display_field] = $val;
                        $this->sortable_fields[] = ['table' => $source_table, 'field' => $source_table_display_field];
                    }

                    // sort by id
                    else {
                        $new_sort[$referenced_table_field] = $val;
                    }

                } else {
                    $new_sort[$key] = $val;
                }

            }
            $this->sort = $new_sort;

        }

        if (empty($this->group_by)) { // is always the same, don't overwrite it for all relation fields
            $this->group_by = sqlIdentQuote([$this->table, $this->primary_key]);
        }

    }

    public function setWhere() {

        $filter = $this->filter;

        // $fields = $this->available_fields;

        // apply filters to fields, that aren't available
        // I'm not 100% sure about the best default behaviour.
        // This fix doesn't work with relation fields yet.
        // * full text search doesn't work for m:n fields
        // * full text search doesn't work if fields are filtered out

        $fields = [];
        foreach ($this->database_columns as $col) {
            $fields[] = ['table' => $this->table, 'field' => $col];
        }

        $fields = array_unique(array_merge(
            $fields,
            $this->available_fields,
            $this->sortable_fields
        ), SORT_REGULAR);

        // fulltext search WHERE foo LIKE %bar%
        // may cause performance issues
        // to do: FTS keys and MATCH AGAINST for better performance
        if ($this->fulltext_search) {

            $i = 0;
            foreach ($fields as $field) {

                $this->where[] = $i == 0 ? "WHERE" : "OR";

                $this->where[] = sqlIdentQuote([$field['table'], $field['field']]) . " LIKE :{$field['field']}_fulltextsearch";

                $this->params[":{$field['field']}_fulltextsearch"] = "%{$this->fulltext_search}%";

                $i++;

            }

        }

        // exact match WHERE foo="bar" AND ...
        elseif ($filter) {

            $i = 0;
            foreach ($fields as $field) {

                if (!empty($filter[$field['field']])) {

                    if (is_array($filter[$field['field']])) {
                        continue;
                        // to do: add mongo filter options like $and, $or etc
                    }

                    $this->where[] = $i == 0 ? "WHERE" : "AND";
                    $this->where[] = sqlIdentQuote([$field['table'], $field['field']]) . " = :" . $field['field'];

                    $this->params[":".$field['field']] = $filter[$field['field']];
                    $i++;

                }

            }

        }

    }

    public function setOrderBy() {

        $fields = array_column($this->available_fields, 'table', 'field');

        if (!empty($this->sortable_fields)) {
            $fields = array_merge(
                $fields,
                array_column($this->sortable_fields, 'table', 'field')
            );
        }

        if ($this->sort) {

            foreach ($this->sort as $field => $direction) {

                if (isset($fields[$field])) {

                    $this->order_by[] = sqlIdentQuote([$fields[$field], $field]) . " " . $direction;

                }

            }

        }

    }

    public function setQuery() {

        $parts = [];

        $parts[] = "SELECT " . implode(', ', $this->select);
        $parts[] = "FROM " . sqlIdentQuote($this->table);

        if (!empty($this->joins))    $parts[] = implode(' ', $this->joins);
        if (!empty($this->where))    $parts[] = implode(' ', $this->where);
        if (!empty($this->group_by)) $parts[] = "GROUP BY " . $this->group_by;

        if (!empty($this->order_by)) {
            $parts[] = "ORDER BY " . implode(', ', $this->order_by);
        } 

        if ($this->limit)            $parts[] = "LIMIT ".$this->offset.", ".$this->limit;

        $this->query = implode(' ', $parts);

    }

    public function getQuery($extended = false) {

        if ($extended) {
            return ['query' => $this->query, 'params' => $this->params, 'normalize' => $this->normalize];
        }

        else {
            return $this->query;
        }

    }

    public function getParams() {

        return $this->params;

    }

    public function getNormalizeInfo() {

        return $this->normalize;

    }
    
    public function is_filtered_out($field_name) {

        // select all
        if (!$this->fields)
            return false;

        // one filter is set to true - don't select any other fields
        if (in_array(true, $this->fields)) {

            if (isset($this->fields[$field_name]) && $this->fields[$field_name] == true)
                return false;

            // return primary_key, too if not explicitly set to false
            if ($field_name == $this->primary_key && ( !isset($this->fields[$this->primary_key]) || $this->fields[$this->primary_key] == true))
                return false;

            return true;

        }

        else {

            if (!isset($this->fields[$field_name]))
                return false;

            if (isset($this->fields[$field_name]) && $this->fields[$field_name] == false)
                return true;

        }

    } // end of is_filtered_out()

    public function getReferences($table_name, $field_name, $type) {

        return $this->app->module('tables')->getReferences($table_name, $field_name, $type);

    } // end of getReferences()

}




if (!function_exists('sqlIdentQuote')) {

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

}