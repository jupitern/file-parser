<?php

namespace Jupitern\Parser;

class FileParser {

    private ?string $filePath = null;
    private string $content = '';

    private ?array $objectFields = null;
    private array $formatters = [];
    private string $fromEncoding;
    private string $toEncoding;
    private ?string $delimiter = null;
    private string $enclosure;
    private string $escape;
    // callables
    private $filter = null;
    private $each = null;
    private $group = null;

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
     * @param string $filePath
     * @param string|null $delimiter
     * @param string $enclosure
     * @param string $escape
     * @return $this
     */
    public function fromFile(string $filePath, string $delimiter = null, string $enclosure = '"', string $escape = '\\'): self
    {
        $this->filePath = $filePath;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape = $escape;

        return $this;
    }

    /**
     * set file to be parsed
     *
     * @param string $content
     * @param string|null $delimiter
     * @param string $enclosure
     * @param string $escape
     * @return $this
     */
    public function fromString(string $content, string $delimiter = null, string $enclosure = '"', string $escape = '\\'): self
    {
        $this->content = $content;
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
    public function setEncoding(string $fromEncoding = 'UTF-8', string $toEncoding = 'UTF-8'): self
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
    public function toObject(array $objectFields = []): self
    {
        $this->objectFields = $objectFields;

        return $this;
    }


    /**
     * format a given line by key using a callable
     * callable must have one param $val and return $val
     *
     * @param string|array $key
     * @param callable $callable
     * @return $this
     */
    public function format(string|array $key, callable $callable): self
    {
        foreach (is_array($key) ? $key : [$key] as $k) {
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
    public function filter(callable $callable): self
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
    public function each(callable $callable): self
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
    public function group(callable $callable): self
    {
        $this->group = $callable;

        return $this;
    }


    /**
     * Parse file and return file contents
     *
     * @return array
     */
    public function parse(): array
    {
        $lines = [];
        $lineNumber = 0;
        $file = $this->filePath ? fopen($this->filePath, "r") : fopen('data://text/plain;base64,' . base64_encode($this->content),'r');;

        while (($line = fgets($file)) !== false) {
            $lineNumber++;

            // change encoding
            if ($this->fromEncoding !== null && $this->toEncoding !== null) {
                $line = iconv($this->fromEncoding, $this->toEncoding, $line);
            }

            if ($this->delimiter !== null) {
                $line = str_getcsv($line, $this->delimiter, $this->enclosure, $this->escape);
            }

            // transform lines to object?
            if (is_array($line) && $this->objectFields !== null) {
                $line = (object)array_combine($this->objectFields, $line);
            }

            // execute callable for each line
            if (is_callable($this->each)) {
                $func = $this->each;
                $line = $func($line, $lineNumber);
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