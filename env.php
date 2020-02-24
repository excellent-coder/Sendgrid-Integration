<?php

$config = [
  //---------------api key//
	'SENDGRID_API_KEY' => 'l,asllasklasionowa',
	'{s_grid_key}',
  //---------------api key//

  //---------------reply name//
	'REPLY_NAME' => '',
	'{re_name}',
  //---------------reply name//

  //---------------reply email//
	'REPLY_EMAIL' => '',
	'{re_email}',
  //---------------from email//

  //---------------from name//
	'FROM_NAME' => 'kjsckdsnckidscnjksdc',
	'{fr_name}',
  //---------------from name//

  //---------------from email//
	'FROM_EMAIL' => 'ksackj nsklcnksd',
	'{fr_email}',
  //---------------from email//
  'status' => 200
];


/**
 * Requirements
 *
 *  And now i'm sending you the color codes,
 *  #243369 this one is for main background rectangle,
 *  #28E3AB this is for buttons color.
 *  #939DFF this is for Hyperlink text
 *  #14004F this one is for Mail confirmation container For *mail has been sent*
 */
foreach ($config as $key => $value) {
  putenv("$key=$value");
}

function env($key, $default = null)
{
  $value = getenv($key);
  if ($value === false) {
    return $default;
  }
  return $value;
}
