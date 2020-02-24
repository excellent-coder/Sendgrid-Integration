<?php
require_once dirname(__DIR__).'/config.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  die(json_encode(['message' => 'Directory Access is forbidden']));
}

require $basePath . '/plugins/File.php';

$files  = dirToArray(storage_path() . 'uploads/csvs');
if ($files) {
  die(json_encode(['files' => $files, 'status' => 200]));
}
