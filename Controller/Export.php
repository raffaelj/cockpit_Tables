<?php

namespace Tables\Controller;

class Export extends \Cockpit\AuthController {

    public function index($table = null) {}

    public function export($table = null, $type = 'json') {
// return func_get_args();
        if (!$this->app->module('tables')->hasaccess($table, 'entries_view')) {
            return $this->helper('admin')->denyRequest();;
        }

        $table   = $table ? $table : $this->app->param('table', '');
        $options = $this->app->param('options', []);
        $type    = $this->app->param('type', $type);

        $table = $this->module('tables')->table($table);
// return $options;
        if (!$table) return false;

        $this->app->trigger('tables.export.before', [$table, &$type, &$options]);
// return $options;
        switch($type) {
            case 'json' : return $this->json($table, $options);           break;
            case 'csv'  : return $this->csv($table, $options);            break;
            case 'ods'  : return $this->sheet($table, $options, 'Ods');   break;
            case 'xls'  : return $this->sheet($table, $options, 'Xls');   break;
            case 'xlsx' : return $this->sheet($table, $options, 'Xlsx');  break;
            default     : return false;
        }

    }

    protected function json($table, $options) {

        $entries = $this->module('tables')->find($table['name'], $options);

        $this->app->response->mime = 'json';
        
        return json_encode($entries, JSON_PRETTY_PRINT);

    } // end of json()

    protected function csv($table, $options) {

        // to do: populate many-to-many fields

        $prettyTitles = $options['pretty'] ?? false;

        $filtered_query = $this->module('tables')->query($table, $options);
        $query = $filtered_query['query'];
        $params = $filtered_query['params'];

        $normalize = !empty($filtered_query['normalize']) ? $filtered_query['normalize'] : null;
        
        $fieldsToNormalize = is_array($normalize) ? array_flip(array_column($normalize, 'field')) : null;
        
// return $normalize;
// return $fieldsToNormalize;

        $filename = $table['name'];

        // set headers
        $this->app->response->headers = [
            'Content-Type: text/csv',
            'Content-Disposition: attachment; filename="'.$filename.'.csv"',
        ];

        $table_headers = [];
        if (!empty($options['fields'])) { // fields filter is active

            foreach($table['fields'] as $field) {
                if (!$this->module('tables')
                        ->is_filtered_out($field['name'], $options['fields'], $table['primary_key']))
                {
                    $table_headers[] = $field['name'];
                    $prettyTableHeaders[] = $prettyTitles && !empty($field['label']) ? $field['label'] : $field['name'];
                }
            }
        }

        else {
            foreach($table['fields'] as $field) {
                $table_headers[] = $field['name'];
                $prettyTableHeaders[] = $prettyTitles && !empty($field['label']) ? $field['label'] : $field['name'];
            }
        }

        // csv output
        ob_start();

        $file = fopen('php://output', 'w');

        // fputcsv($file, $table_headers);
        fputcsv($file, $prettyTableHeaders);

        $stmt = $this('db')->run($query, $params);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

            // populate related fields
            if ($normalize) {
                foreach ($row as $k => &$v) {

                    if (isset($fieldsToNormalize[$k])) {

                        $arr = $this->app->module('tables')->normalizeGroupConcat([[$k => $v]], $normalize);
                        $v = implode(', ', $arr[0][$k] ?? []);

                    }
                }
            }

            fputcsv($file, $row);
        }

        fclose($file);

        return ob_get_clean();

    } // end of csv()
    
    protected function sheet($table = [], $options = [], $type = 'Ods') {

        $user = $this->app->module('cockpit')->getUser();

        $filename = $table['name'];

        $description = "Exported with Cockpit Tables Addon";

        $prettyTitles = $options['pretty'] ?? false;

        if (!empty($table['description']))
            $description .= "\r\n\r\n" . $table['description'];
        
        if (!empty($options))
            $description .= "\r\n\r\nUser defined filter options:\r\n";

        foreach ($options as $key => $val) {
            $description .= $key . ': ' . json_encode($val) . "\r\n";
        }

        $opts = [
            'title'       => !empty($table['label']) ? $table['label'] : $table['name'],
            'creator'     => !empty($user['name']) ? $user['name'] : $user['user'],
            'description' => trim($description),
        ];

        $spreadsheet = new \SheetExport($opts);

        // table headers
        $c = 'A';
        $r = '1';
        foreach($table['fields'] as $field) {

            if (empty($options['fields']) ||
                !$this->module('tables')
                    ->is_filtered_out($field['name'], $options['fields'], $table['primary_key']))
            {
                // $spreadsheet->setCellValue($c.$r, $field['name']);
                $spreadsheet->setCellValue($c.$r, $prettyTitles && !empty($field['label']) ? $field['label'] : $field['name']);
                $c++;
            }
        }

        // table contents
        $entries = $this->module('tables')->find($table['name'], $options);

        $c = 'A';
        $r = '2';
        foreach($entries as $entry) {

            foreach($table['fields'] as $field) {

                if (isset($entry[$field['name']]) && is_array($entry[$field['name']])) {
                    $entry[$field['name']] = implode(', ', $entry[$field['name']]);
                }

                if (empty($options['fields']) ||
                    !$this->module('tables')
                        ->is_filtered_out($field['name'], $options['fields'], $table['primary_key']))
                {
                    $spreadsheet->setCellValue($c.$r, $entry[$field['name']] ?? '');
                    $c++;
                }

            }

            $c = 'A';
            $r++;

        }

        // write file and exit
        $spreadsheet->write($type, $filename);

    } // end of sheet()

}
