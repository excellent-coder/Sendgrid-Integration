<?php
require_once dirname(__DIR__).'/config.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  die(header('location: /'));
} elseif (empty($_FILES['csvs'])) {
  die(json_encode(['message' => 'No File choosen, Please trey again']));
}

$file = $_FILES['csvs'];
// die(json_encode($file));
require $basePath . '/plugins/File.php';

$success = 0;
$error = 0;

if (isset($_FILES['csvs']) && !empty(trim($_FILES['csvs']['name'][0]))) {

  if (count($_FILES['csvs']['name']) > 0) {

    $file_names = $_FILES['csvs']['name'];
    $file_types = $_FILES['csvs']['type'];
    $file_tmps = $_FILES['csvs']['tmp_name'];
    $file_errors = $_FILES['csvs']['error'];
    $file_sizes = $_FILES['csvs']['size'];
    // die(json_encode(['message' => [$file_names[0], $file_types[0], $file_tmps[0], $file_errors[0]]]));

    for ($i = 0; $i < count($file_names); $i++) {
      try {
        File::upload(
          [
            'name' => $file_names[$i],
            'type' => $file_types[$i],
            'tmp_name' => $file_tmps[$i],
            'error' => $file_errors[$i],
            'size' => $file_sizes[$i]
          ],
          'uploads/csvs',
          999999999,
          ['application' => ['vnd.ms-excel'], 'text' => ['csv']]
        );
        $success++;
      } catch (Exception $e) {
        $error++;
      }
    }
  }
}

// if (File::upload($file, 'uploads/csvs', 300000, ['application' => ['vnd.ms-excel'], 'text' => ['csv']])) {
die(json_encode(['message' => " $success Files Uploaded successfully with $error Failure", 'status' => 200]));
// }
