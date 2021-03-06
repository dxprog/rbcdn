<?php

class Responder {
  public function processRequest(stdClass $params) {
    // If the request method is HEAD, we're being probed to see
    // if the server has a copy of the requested file. This script
    // only ever executes if the reqeusted file doesn't exist, so
    // immediately return a 404 so the callee (likely this script on a
    // different machine) can move on to the next source. This will
    // allow all rbcdn responders to refer to each other without creating
    // an infinite HTTP request loop. More info on that:
    // https://dxprog.com/entry/fun-with-load-balancers-and-running-on-lean-disk-space/
    if ($params->server['REQUEST_METHOD'] === 'HEAD') {
      $this->send404();
    }

    $pathInfo = self::extractPathInfo($params->queryString['_q']);

    // Determine the processor to use for this request
    $processor = null;
    if ($pathInfo->isMobile) {
      $processor = new MobileFetch($pathInfo->path, $params->sources, $params->localPath);
    } else {
      $processor = new ImageFetch($pathInfo->path, $params->sources, $params->localPath);
    }

    // If anything is amiss, just 404
    if (!$processor->fetch()) {
      $this->send404();
    } else {
      // Save the image and send it along the output
      $processor->save();
      OutputWriter::addHeader('Content-Type', $processor->contentType);
      OutputWriter::write($processor->imageData);
    }
  }

  /**
   * Given an image path, creates a normalized path
   * and determines if it's a mobile targeted image
   */
  private static function extractPathInfo($url) {
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

  private function send404() {
    OutputWriter::addHeader('HTTP/1.1 404 Not Found');
    OutputWriter::end();
  }
}