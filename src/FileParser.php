<?php

namespace Jupitern\Parser;

/*
 * File Parser
 * read, filter, parse and format {csv, tsv, dsv, variable-length-delimited} and other txt files
 *
 * Author: Nuno Chaves <nunochaves@sapo.pt>
 * */

class FileParser {

    private $filePath = null;
    private $objectFields = null;
    private $formatters = [];
    private $filter = null;
    private $each = null;
    private $group = null;
    private $fromEncoding = null;
    private $toEncoding = null;
    private $delimiter;
    private $enclosure;
    private $escape;

    /**
     * @return static
     */
    public static function instance()
    {
        return new static();
    }


    /**
     * set file to be parsed
     *
     * @param $filePath
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * @return $this
     */
    public function setFile($filePath, $delimiter = null, $enclosure = '"', $escape = '\\')
    {
        $this->filePath = $filePath;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape = $escape;

        return $this;
    }

    /**
     * set encoding conversion options
     *
     * @param string $fromEncoding
     * @param string $toEncoding
     * @return $this
     */
    public function setEncoding($fromEncoding = 'UTF-8', $toEncoding = 'UTF-8')
    {
        $this->fromEncoding = $fromEncoding;
        $this->toEncoding = $toEncoding;

        return $this;
    }


    /**
     * return file as array of objects
     *
     * @param array $objectFields Object field names
     * @return $this
     */
    public function toObject(array $objectFields = [])
    {
        $this->objectFields = $objectFields;

        return $this;
    }


    /**
     * format a given line by key using a callable
     * callable must have one param $val and return $val
     *
     * @param $key
     * @param callable $callable
     * @return $this
     */
    public function format($key, callable $callable)
    {
        foreach ((array)$key as $k) {
            $this->formatters[$k][] = $callable;
        }

        return $this;
    }


    /**
     * set a callable to be called on each line to filter lines to retrieve
     * callable must have params ($line, $number) and return a boolean
     *
     * @param callable $callable
     * @return $this
     */
    public function filter(callable $callable)
    {
        $this->filter = $callable;

        return $this;
    }


    /**
     * set a callable to be called on each line
     * callable must have one param $line and return $line
     *
     * @param callable $callable
     * @return $this
     */
    public function each(callable $callable)
    {
        $this->each = $callable;

        return $this;
    }


    /**
     * set grouping rules to return file contents grouped in an associative array
     * callable must have one param $line and return string (grouping key)
     *
     * @param callable $callable
     * @return $this
     */
    public function group(callable $callable)
    {
        $this->group = $callable;

        return $this;
    }


    /**
     * Parse file and return file contents
     *
     * @return array
     */
    public function parse()
    {
        $lines = [];
        $lineNumber = 0;
        $file = fopen($this->filePath, "r");

        while (($line = fgets($file)) !== false) {
            $lineNumber++;

            if ($this->delimiter !== null) {
                $line = str_getcsv($line, $this->delimiter, $this->enclosure, $this->escape);
                // change encoding
                if ($this->fromEncoding !== null && $this->toEncoding !== null) {
                    $line = array_map(function($val) {
                        return iconv($this->fromEncoding, $this->toEncoding, $val);
                    }, $line);
                }
            } else {
                $line = iconv($this->fromEncoding, $this->toEncoding, $line);
            }

            // transform lines to object?
            if ($this->objectFields !== null) {
                $line = (object)array_combine($this->objectFields, $line);
            }

            // execute callable for each line
            if (is_callable($this->each)) {
                $func = $this->each;
                $line = $func($line);
            }

            // execute callable to filter line
            if (is_callable($this->filter)) {
                $func = $this->filter;
                if (!(boolean)$func($line, $lineNumber)) {
                    continue;
                }
            }

            foreach ($this->formatters as $key => $callables) {
                foreach ($callables as $callable) {
                    if (is_object($line)) {
                        $line->{$key} = $callable($line->{$key});
                    } else {
                        $line[$key] = $callable($line[$key]);
                    }
                }
            }

            if (is_callable($this->group)) {
                $func = $this->group;
                $lines[$func($line)][] = $line;
            } else {
                $lines[] = $line;
            }
        }

        return $lines;
    }

}
