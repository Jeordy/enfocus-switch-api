<?php

namespace App;

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
echo 'Example with CURL:<br />';
var_dump($token);

/* Example with GUZZLE */
$switch = new SwitchServiceGuzzle();

// Login and get token from Switch Server
$result = $switch->login($username, $password);
$array = json_decode($result, true);
$token = $array['token'];

echo '<pre>';
echo 'Example with GUZZLE:<br />';
var_dump($token);
