<?php

require_once dirname(__DIR__).'/config.php';

ini_set('max_execution_time', '0'); // for infinite time of execution
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  die(header('location: /'));
} elseif (empty($_POST['message'])) {
  die(json_encode(['message' => 'Cannot send empty message']));
}
// elseif (empty($_SESSION['admin'])) {
//   die(json_encode(['message' => 'Permission not allowed']));
// }
require $basePath . '/vendor/autoload.php';
require $basePath . '/plugins/File.php';
require $basePath . '/env.php';
$the_big_array = [];


if (!empty(trim($_POST['csv']))) {
  $post_csv = true;
  // csv posted
  // read csv
  $the_big_array = [];

  $csv = trim($_POST['csv']);
  $file = storage_path() . 'uploads/csvs/' . $csv;


  $open = fopen($file, "r");

  if ($open !== FALSE) {

  $cols = fgetcsv($open);

  foreach ($cols as $key => $value) {
    // calculate for email index

    if ($value == 'email') {
      $seen_email = true;
      $email_index = $key;
    } elseif ($value == 'emails') {
      $seen_email = true;
      $email_index = $key;
    } elseif (strpos($value, 'email') !== false && empty($seen_email)) {
      $email_index = $key;
      $seen_email = true;
    } elseif (empty($seen_email)) {
      $email_index = false;
    }

    // calculate for name index
    if ($value == 'name') {
      $seen_name = true;
      $name_index = $key;
    } elseif ($value == 'names') {
      $seen_name = true;
      $name_index = $key;
    } elseif (strpos($value, 'name') !== false && empty($seen_name)) {
      $name_index = $key;
      $seen_name = true;
    } elseif (empty($seen_name)) {
      $name_index = false;
    }
  }


  //   // open file

    while (($data = fgetcsv($open, ",")) !== FALSE) {
      // read the file

      // for ($i = 0; $i < count($cols); $i++) {
      //   $user[$cols[$i]] = $data[$i];
      // }

      if ($name_index !== false) {
        $user['name'] = $data[$name_index];
      } else {
        if ($email_index !== false) {
          $user['name'] = explode('@', $data[$email_index])[0];
        }
      }
      if ($email_index !== false) {
        $user['email'] = $data[$email_index];
      }
      if (!empty($user['email'])) {
        $the_big_array[] = $user;
      }
    }

    fclose($open);
  }
}

$apiKey = env('SENDGRID_API_KEY');

$sg = new \SendGrid($apiKey);
$message = trim($_POST['message']);
// $from_email = trim($_POST['from_email']);
$from_email = env('FROM_EMAIL');
// $from_name = trim($_POST['from_name']);
$from_name = env('FROM_NAME');


$subject = trim($_POST['subject']);

// calculate time;
$day = (int) trim($_POST['day']);
$hour = (int) trim($_POST['hour']);
// $minute = (int) trim($_POST['minute']);
// $second = (int) trim($_POST['second']);
$useIntervals = false;


$seconds = (24 * 60 * 60 * $day) + ($hour * 60 * 60);
//  + ($minute * 60) + $second;

/*
  // scheduele with intervals
 $time_interval = false;
  $email_for_interval = false;
  $total_emails_to_send = false;

  if (!empty($_POST['schedule'])) {
  $useIntervals = true;
  $email_for_interval = (int) trim($_POST['email_count']);
  $time_for_interval = (int) trim($_POST['time_interval']);
  $time_verb = trim($_POST['time_verb']);

  $for_times = (int) trim($_POST['for_times']);

  if ($for_times === 0) {
    $for_times = 99999999999999;
  }

  if ($email_for_interval === 0) {
    $email_for_interval = 99999999999999;
  }

  $total_emails_to_send = $for_times * $email_for_interval;

  // claculate time intervals
  if ($time_verb == 'seconds') {
    $time_interval = $time_for_interval;
  } elseif ($time_verb == 'minutes') {
    $time_interval =  $time_for_interval * 60;
  } else {
    $time_interval = $time_for_interval * 60 * 60;
  }

  if (intval($time_interval + $seconds)  > (72 * 60 * 60)) {
    die(json_encode(['message' => 'Maximun schedule time cannot be more than 72 hours']));
  }
  }
*/

// $seconds
if ($seconds > (72 * 60 * 60)) {
  die(json_encode(['message' => 'Maximun schedule time cannot be more than 72 hours']));
}

$sendAt = strtotime("+ $seconds seconds");

// start the schedule at this time
$schedule_start = $sendAt;


$to_receivers = [];

if (!empty($_POST['to'])) {
  $receivers  = $_POST['to'];
  $receivers = explode(',', $receivers);
  foreach ($receivers as $value) {
    $to_receivers[] = ['name' => explode('@', $value)[0], 'email' => $value];
  }
}

$receivers = array_merge($to_receivers, $the_big_array);

// die(json_encode($receivers));

// $times_to_send = count($receivers) / $email_for_interval;

// function timeToSend($index, $intervals, $time_loop)
// {
//   global $schedule_start, $seconds, $useIntervals;


//   if ($useIntervals === false) {
//     return $schedule_start;
//   }

//   if ($index < $intervals) {
//     return $schedule_start;
//   }

//   $x = floor($index / $intervals);
//   $y = ((int) $x * intval($time_loop)) + $seconds;

//   if (intval($y) > (72 * 60 * 60)) {
//     $y = (72 * 60 * 60);
//   }
//   return strtotime("+ $y seconds");
// }

$personalizations = [
  [
    'to' => [['email' => $from_email, 'name' => $from_name]],
    'subject' => $subject,
    'send_at' => $schedule_start
  ]
];

// die(json_encode($receivers));
foreach ($receivers as $key => $user) {
  if (empty($user['email'])) {
    continue;
  }

  if(filter_var($user['email'], FILTER_VALIDATE_EMAIL) === false){
    continue;
  }
  // if ($useIntervals && $key === $total_emails_to_send) {
  //   break;
  // }
  $personalizations[] = [
    'to' => [['email' => $user['email'], 'name' => $user['name']]],
    'subject' => $subject,
    'send_at' => $sendAt
    // 'send_at' => timeToSend($key, $email_for_interval, $time_interval)
  ];
}

// die(json_encode($personalizations));

if (count($personalizations) < 2) {
  die(json_encode(['message' => 'Job terminated! No emails provided']));
}
// $zz_personalizations = [];

// for ($i = 0; $i < count($personalizations); $i++) {
//   $x = floor($i / 1000);
//   $y_{
//     $x}[] = $personalizations[$i];
//   $zz_personalizations[$x] = $y_{
//     $x};
// }

// die(json_encode($zz_personalizations));

// for ($i = 0; $i < count($zz_personalizations); $i++) {

$body =  [
  'personalizations' => $personalizations,
  'content' => [
    ['type' => 'text/html', 'value' => $message]
  ],
  'from' => ['email' => $from_email, 'name' => $from_name],
  'reply_to' => ['email' => env('REPLY_EMAIL'), 'name' => env('REPLY_NAME')]

];

$body = json_decode(json_encode($body));

// die(json_encode($body));
try {
  $response = $sg->client->mail()->send()->post($body);
  if (in_array($response->statusCode(), [202])) {
    // $success = true;
    die(json_encode(['message' => "message sent returned status is  " . $response->statusCode(), 'status' => 200]));
  } else {
    $resBody = strlen($response->body()) > 2 ? $response->body() : 'Not Available';
    die(json_encode(['message' => "Error with status "
      . $response->statusCode() . '  and message is  ' . $resBody]));
  }
} catch (Exception $e) {
  die(json_encode(['message' => 'Caught exception: ' . $e->getMessage(), 'status' => 400]));
}

// if ($i + 1 === count($zz_personalizations) && $success === true) {
// die(json_encode(['message' => 'Messages has been sent as scheduled', 'status' => 200]));
// } else {
// die(json_encode(['message' => 'Something went wrong', 'status' => 400]));
// }
// }


die(json_encode(['message' => 'Something went wrong, Message not sent']));
