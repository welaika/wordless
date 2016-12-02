<?php
// $Id$

class PageRequest
{
    private $parsed;
    
    public function PageRequest($raw)
    {
        $statements = explode('&', $raw);
        $this->parsed = array();
        foreach ($statements as $statement) {
            if (strpos($statement, '=') === false) {
                continue;
            }
            $this->parseStatement($statement);
        }
    }
    
    private function parseStatement($statement)
    {
        list($key, $value) = explode('=', $statement);
        $key = urldecode($key);
        if (preg_match('/(.*)\[\]$/', $key, $matches)) {
            $key = $matches[1];
            if (! isset($this->parsed[$key])) {
                $this->parsed[$key] = array();
            }
            $this->addValue($key, $value);
        } elseif (isset($this->parsed[$key])) {
            $this->addValue($key, $value);
        } else {
            $this->setValue($key, $value);
        }
    }
    
    private function addValue($key, $value)
    {
        if (! is_array($this->parsed[$key])) {
            $this->parsed[$key] = array($this->parsed[$key]);
        }
        $this->parsed[$key][] = urldecode($value);
    }
    
    private function setValue($key, $value)
    {
        $this->parsed[$key] = urldecode($value);
    }
    
    public function getAll()
    {
        return $this->parsed;
    }
    
    public function get()
    {
        $request = new PageRequest($_SERVER['QUERY_STRING']);
        return $request->getAll();
    }
    
    public function post()
    {
        global $HTTP_RAW_POST_DATA;
        $request = new PageRequest($HTTP_RAW_POST_DATA);
        return $request->getAll();
    }
}
