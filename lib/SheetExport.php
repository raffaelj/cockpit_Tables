<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SheetExport {

    protected $spreadsheet;
    // protected $writeTableHeaders = true;

    public $mimeTypes = [
        'xls'   => 'application/vnd.ms-excel',
        'xlsx'  => 'application/vnd.ms-excel',
        'ods'   => 'application/vnd.oasis.opendocument.spreadsheet',

        // maybe useful in a future version
        'csv'   => 'text/csv',
        'json'  => 'application/json',
        'txt'   => 'text/plain',
        'xml'   => 'text/xml',
        'zip'   => 'application/zip',
        'gzip'  => 'application/x-gzip',
        'pdf'   => 'application/pdf',
    ];

    public function __construct($options = []) {

        $this->spreadsheet = new Spreadsheet();

        if (!empty($options)) {
            $this->setOptions($options);
        }

        // reset default value for company
        $this->setCompany($options['company'] ?? '');

    }

    public function setOptions($options) {

        // if (isset($options['writeTableHeaders'])) {
            // $this->writeTableHeaders = $options['writeTableHeaders'];
        // }


        if (!empty($options['title'])) {
            $this->setTitle($options['title']);
        }

        if (!empty($options['creator'])) {
            $this->setCreator($options['creator']);
        }

        if (!empty($options['modified_by'])) {
            $this->setLastModifiedBy($options['modified_by']);
        }

        if (!empty($options['description'])) {
            $this->setDescription($options['description']);
        }

        if (!empty($options['subject'])) {
            $this->setSubject($options['subject']);
        }

        if (!empty($options['keywords'])) {
            $this->setKeywords($options['keywords']);
        }

        return $this;

    }

    public function setCreator($username = '') {

        $this->spreadsheet->getProperties()
            ->setCreator($username);

        return $this;

    }

    public function setLastModifiedBy($username = '') {

        $this->spreadsheet->getProperties()
            ->setLastModifiedBy($username);

        return $this;

    }

    public function setTitle($title = '') {

        $this->spreadsheet->getProperties()
            ->setTitle($title);

        return $this;

    }

    public function setSubject($subject = '') {

        $this->spreadsheet->getProperties()
            ->setSubject($subject);

        return $this;

    }

    public function setDescription($description = '') {

        $this->spreadsheet->getProperties()
            ->setDescription($description);

        return $this;

    }

    public function setKeywords($keywords = '') {

        $this->spreadsheet->getProperties()
            ->setKeywords($keywords);

        return $this;

    }

    public function setCompany($title = '') {

        $this->spreadsheet->getProperties()
            ->setCompany($title);

        return $this;

    }

    public function setCellValue($cell, $value) {

        $this->spreadsheet->getActiveSheet()
            ->setCellValue($cell, $value);

        return $this;

    }

    public function save($type = 'Ods', $filename = 'sheet') {

        header('Content-Type: ' . $this->mimeTypes[strtolower($type)]);
        header('Content-Disposition: attachment;filename="'.$filename.'.'.strtolower($type).'"');

        $writer = IOFactory::createWriter($this->spreadsheet, $type);
        $writer->save('php://output');

        exit;

    }

    // alias for save()
    public function write($type = 'Ods', $filename = 'sheet') {
        $this->save($type, $filename);
    }

}
