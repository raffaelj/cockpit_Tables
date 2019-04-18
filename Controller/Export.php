<?php

namespace Tables\Controller;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Export extends \Cockpit\AuthController {
    
    public function index($table = null) {}

    public function export($table = null, $type = 'json') {

        if (!$this->app->module('cockpit')->hasaccess('tables', 'manage')) {
            return false;
        }

        $table   = $table ? $table : $this->app->param('table', '');
        $options = $this->app->param('options', []);

        $table = $this->module('tables')->table($table);

        if (!$table) return false;

        if (!$this->module('tables')->hasaccess($table['name'], 'entries_view')) {
            return $this->helper('admin')->denyRequest();
        }

        if (!method_exists($this, $type)) {
            $type = 'json';
        }

        return $this->$type($table, $options);

    }

    protected function json($table, $options) {

        $entries = $this->module('tables')->find($table['name'], $options);

        $this->app->response->mime = 'json';
        
        return json_encode($entries, JSON_PRETTY_PRINT);

    } // end of json()

    protected function csv($table, $options) {

        $filtered_query = $this->module('tables')->filterToQuery($table, $options);
        $query = $filtered_query['query'];
        $params = $filtered_query['params'];

        $filename = $table['name'];

        // set headers
        $this->app->response->headers = [
            'Cache-Control: must-revalidate, post-check=0, pre-check=0',
            'Content-Description: File Transfer',
            'Content-Type: text/csv',
            'Content-Disposition: attachment; filename="'.$filename.'.csv"',
            'Pragma: no-cache',
            'Expires: 0'
        ];

        // csv output
        ob_start();

        $file = fopen('php://output', 'w');

        $headers = [];
        foreach($table['fields'] as $field) {
            $headers[] = $field['name'];
        }

        fputcsv($file, $headers);

        $stmt = $this('db')->run($query, $params);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC))
            fputcsv($file, $row);

        fclose($file);

        return ob_get_clean();

    } // end of json()
    
    protected function ods($table, $options) {
        
        $spreadsheet = new Spreadsheet();
        
        $user = $this->app->module('cockpit')->getUser();

        $filename = $table['name'];
        
        $spreadsheet->getProperties()
            ->setCreator($user['name'] == $user['user'])
            ->setLastModifiedBy($user['name'] == $user['user'])
            ->setTitle($table['label'] ?? $table['name'])
            // ->setSubject('')
            // ->setDescription('')
            // ->setKeywords('')
            // ->setCategory('')
            ;
        
        // table headers
        $c = 'A';
        $r = '1';
        foreach($table['fields'] as $field) {
            $spreadsheet->getActiveSheet()->setCellValue($c.$r, $field['name']);
            $c++;
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

                $spreadsheet->getActiveSheet()->setCellValue($c.$r, $entry[$field['name']] ?? '');
                $c++;
            }

            $c = 'A';
            $r++;

        }

        header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
        header('Content-Disposition: attachment;filename="'.$filename.'.ods"');

        $writer = IOFactory::createWriter($spreadsheet, 'Ods');
        $writer->save('php://output');

        $this->app->stop();
    }
    
}
