<?php

class WordlessCompileException extends Exception
{
  public function __construct($message, $output = null) {
    $this->output = $output;
    parent::__construct($message);
  }

  public function __toString() {
    return __CLASS__ . ": [{$this->code}]: {$this->message}\n\nOutput error:\n\n{$this->output}";
  }

  public function getOutput() {
    return $this->output;
  }

}
