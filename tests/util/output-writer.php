<?php

class OutputWriter {
  public $headers = [];
  public $responseEnded = false;
  public $output = '';

  public static function getInstance() {
    static $instance;
    if (!$instance) {
      $instance = new OutputWriter();
    }
    return $instance;
  }

  public static function reset() {
    $instance = self::getInstance();
    $instance->headers = [];
    $instance->responseEnded = false;
    $instance->output = '';
  }

  public static function addHeader($header, $value = true) {
    self::getInstance()->headers[$header] = $value;
  }

  public static function end() {
    self::getInstance()->responseEnded = true;
  }

  public static function write($output) {
    self::getInstance()->output .= $output;
  }
}