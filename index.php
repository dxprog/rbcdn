<?php

require('./config.php');
require('./src/util/output-writer.php');
require('./src/util/http-helpers.php');
require('./src/responder.php');
require('./src/image-fetch.php');
require('./src/mobile-fetch.php');

$responder = new Responder();
$responder->processRequest((object) [
  'sources' => $IMAGE_SOT,
  'localPath' => LOCAL_PATH,
  'server' => $_SERVER,
  'queryString' => $_GET
]);