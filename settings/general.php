<?php
require_once dirname(__DIR__) . '/config.php';
// updating api key
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $key = trim($_POST['key']);
  $env = $basePath . '/env.php';

  $file = file_get_contents($env);
  $file = str_replace('SENDGRID_API_KEY', 'OLD_KEY', $file);
  $file = str_replace(["'{s_grid_key}'"], ["'SENDGRID_API_KEY' => '$key',\r\n\t'{s_grid_key}'"], $file);

  $fname = trim($_POST['from_name']);
  $femail = trim($_POST['from_email']);
  $rname = trim($_POST['reply_name']);
  $remail = trim($_POST['reply_email']);


  $file = str_replace(['REPLY_NAME', 'REPLY_EMAIL'], ['OLD_R_NAME', 'OLD_R_EMAIL'], $file);
  $file = str_replace(["'{re_email}'", "'{re_name}'"], ["'REPLY_EMAIL' => '$remail',\r\n\t'{re_email}'", "'REPLY_NAME' => '$rname',\r\n\t'{re_name}'"], $file);
  $file = str_replace(["'{fr_email}'", "'{fr_name}'"], ["'FROM_EMAIL' => '$femail',\r\n\t'{fr_email}'", "'FROM_NAME' => '$fname',\r\n\t'{fr_name}'"], $file);
  if (file_put_contents($env, $file)) {
    die(json_encode(['message' => 'Settings Updated', 'status' => 200]));
  }
  die(json_encode(['message' => 'No update has been made']));
}
header('location: /');
