<?php
App::uses('AppHelper', 'View/Helper');

/**
 * Created by PhpStorm.
 * User: daikihirakata
 * Date: 2014/03/21
 * Time: 23:45
 */
class CsvHelper extends AppHelper
{

    var $delimiter = ',';
    var $enclosure = '"';
    var $filename = 'Export.csv';
    var $line = array();
    var $buffer;

    public function __construct(View $view, $settings = array())
    {
        parent::__construct($view, $settings);
        $this->clear();
    }

    function clear()
    {
        $this->line = array();
        $this->buffer = fopen('php://temp/maxmemory:' . (5 * 1024 * 1024), 'r+');
    }

    function addField($value)
    {
        $this->line[] = $value;
    }

    function endRow()
    {
        $this->addRow($this->line);
        $this->line = array();
    }

    function addRow($row)
    {
        $this->encfputscv($this->buffer, $row, $this->delimiter, $this->enclosure);
    }

    function encfputscv($fp, $row, $delimiter = ',', $enclosure = '"', $eol = "\r\n")
    {
        $tmp = array();
        foreach ($row as $v) {
            $v = str_replace('"', '""', $v);
            //改行コードを変換
            $v = str_replace("\n", PHP_EOL, $v);
            $tmp[] = $enclosure . $v . $enclosure;
        }
        $str = implode($delimiter, $tmp) . $eol;
        return fwrite($fp, $str);
    }

    function renderHeaders()
    {
        header("Content-type:application/vnd.ms-excel");
        header("Content-disposition:attachment;filename=" . $this->filename);
    }

    function setFilename($filename)
    {
        $this->filename = $filename;
        if (strtolower(substr($this->filename, -4)) != '.csv') {
            $this->filename .= '.csv';
        }
    }

    function render($outputHeaders = true, $to_encoding = null, $from_encoding = "auto")
    {
        if ($outputHeaders) {
            if (is_string($outputHeaders)) {
                $this->setFilename($outputHeaders);
            }
            $this->renderHeaders();
        }
        rewind($this->buffer);
        $output = stream_get_contents($this->buffer);
        if ($to_encoding) {
            $output = mb_convert_encoding($output, $to_encoding, $from_encoding);
        }
        return $this->output($output);
    }

    function defaultRender($th = [], $td = [], $filename)
    {
        $this->addRow($th);
        if (!empty($td)) {
            foreach ($td as $td_v) {
                foreach ($td_v as $v) {
                    $this->addField($v);
                }
                $this->endRow();
            }
        }
        $this->setFilename($filename);
        return $this->render(true, 'SJIS-win', 'UTF-8');
    }
}
