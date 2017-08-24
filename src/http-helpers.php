<?php

/**
 * Sends a 404 header and ends the request
 */
function send404() {
  header('HTTP/1.1 404 Not Found');
  exit;
}

/**
 * Given an image path, creates a normalized path
 * and determines if it's a mobile targeted image
 */
function extractPathInfo($url) {
  $retVal = (object)[
    'isMobile' => false,
    'path' => '/'
  ];

  // Normalize the path and determine if this is a mobile target
  // Mobile is denoted by a path starting with /m/
  $info = pathinfo($url);
  $dir = trim(str_replace('/', ' ', $info['dirname']));
  if (strlen($dir) > 0) {
    $dir = explode(' ', $dir);
    if ($dir[0] === 'm') {
      $retVal->isMobile = true;
      array_shift($dir);
    }

    if (count($dir) > 0) {
      $retVal->path .= implode('/', $dir) . '/';
    }
  }

  $retVal->path .= $info['basename'];
  return $retVal;
}