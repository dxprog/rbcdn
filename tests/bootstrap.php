<?php

require('./config.php');
require('./tests/util/output-writer.php');
require('./src/util/http-helpers.php');
require('./src/responder.php');
require('./src/image-fetch.php');
require('./src/mobile-fetch.php');

const SOURCE_1_PNG = '/anime1.png';
const SOURCE_2_JPG = '/tsunderes/anime2.jpg';
const SOURCE_1_GIF = '/anime3.gif';
const SOURCES = [
  './tests/sources/chitoge.cdn.awwni.me',
  './tests/sources/taiga.cdn.awwni.me'
];