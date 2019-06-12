<?php

namespace App;

ini_set('display_errors', true);
require __DIR__.'/../vendor/autoload.php';

use App\Service\SwitchServiceCurl;
use App\Service\SwitchServiceGuzzle;

$username = 'demo';
$password = 'demo';

/* Example with CURL */
$switch = new SwitchServiceCurl();

// Login and get token from Switch Server
$result = $switch->login($username, $password);
$array = json_decode($result, true);
$token = $array['token'];

echo '<pre>';
echo '<strong>Example with CURL:</strong><br />';
var_dump('Token from login: ' . $token);
echo '<strong>Submit points:</strong><br />';
$submitPoint = $switch->listSubmitPoints($token);
var_dump(json_decode($submitPoint));

var_dump($switch->jobSubmit($token, $submitPoint));

echo '<hr />';

/* Example with GUZZLE */
// $switch = new SwitchServiceGuzzle();

// // Login and get token from Switch Server
// $result = $switch->login($username, $password);
// $array = json_decode($result, true);
// $token = $array['token'];

// echo '<pre>';
// echo '<strong>Example with GUZZLE:</strong><br />';
// var_dump('Token from login: ' . $token);
// echo '<strong>Submit points:</strong><br />';
// $submitPoint = $switch->listSubmitPoints($token);
// var_dump(json_decode($submitPoint));

// var_dump($switch->jobSubmit($token, $submitPoint));
