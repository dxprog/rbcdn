<?php

require('./src/http-helpers.php');

// If the request method is HEAD, we're being probed to see
// if the server has a copy of the requested file. This script
// only ever executes if the reqeusted file doesn't exist, so
// immediately return a 404 so the callee (likely this script on a
// different machine) can move on to the next source. This will
// allow all rbcdn responders to refer to each other without creating
// an infinite HTTP request loop. More info on that:
// https://dxprog.com/entry/fun-with-load-balancers-and-running-on-lean-disk-space/
if ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
  send404();
}

require('./config.php');
require('./src/image-fetch.php');

$pathInfo = extractPathInfo($_GET['_q']);

// Determine the processor to use for this request
$processor = null;
if ($pathInfo->isMobile) {
  require('./src/mobile-fetch.php');
  $processor = new MobileFetch($pathInfo->path, $IMAGE_SOT);
} else {
  $processor = new ImageFetch($pathInfo->path, $IMAGE_SOT);
}

// If anything is amiss, just 404
if (!$processor->fetch()) {
  send404();
} else {
  // Save the image and send it along the output
  $processor->save();
  header('Content-Type: ' . $processor->contentType);
  echo $processor->imageData;
}
