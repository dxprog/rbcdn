<?php

class OutputWriter {
  public static function addHeader($header, $value = null) {
    $output = $header . ($value ? ': ' . $value : '');
    header($output);
  }

  public static function end() {
    exit;
  }

  public static function write($output) {
    echo $output;
  }
}