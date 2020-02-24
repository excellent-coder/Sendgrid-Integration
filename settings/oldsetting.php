<?php
require_once dirname(__DIR__).'/config.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  die();
}
require  $basePath . '/env.php';
die(json_encode($config));
