<?php

function getHeaders($url) {
  // If the URL is an HTTP request, just use get_headers. Otherwise,
  // check the local file system and mimic the output.
  if (strpos($url, 'http') === 0) {
    return @get_headers($url, 1);
  } else {
    $retVal = [
      0 => 'HTTP/1.1 404 Not Found',
      'Content-Length' => 0
    ];

    if (file_exists($url)) {
      $retVal[0] = 'HTTP/1.1 200 OK';
      $retVal['Content-Length'] = filesize($url);
      $retVal['Content-Type'] = mime_content_type($url);
    }

    return $retVal;
  }
}