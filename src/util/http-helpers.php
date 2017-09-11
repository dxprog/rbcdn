<?php

function getHeaders($url) {
  $retVal = null;

  // If the URL is an HTTP request, do a HEAD request. Otherwise,
  // check the local file system and mimic the output.
  if (strpos($url, 'http') === 0) {
    $context  = stream_context_create([ 'http' => [ 'method' => 'HEAD' ] ]);
    $stream = @fopen($url, 'rb', false, $context);
    if ($stream) {
      $data = @stream_get_meta_data($stream);
      if ($data) {
        $retVal = [ 0 => '' ];
        $headers = $data['wrapper_data'];
        foreach ($headers as $header) {
          if (strpos($header, 'HTTP') === 0) {
            $retVal[0] = $header;
          } else {
            $kvp = explode(':', $header, 2);
            $retVal[$kvp[0]] = $kvp[1];
          }
        }
      }
    }
    fclose($stream);
  } else {
    if (file_exists($url)) {
      $retVal = [
        0 => 'HTTP/1.1 200 OK',
        'Content-Length' => filesize($url),
        'Content-Type' => mime_content_type($url)
      ];
    }
  }

  return $retVal;
}