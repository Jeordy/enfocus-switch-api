<?php

namespace App;

require __DIR__.'/../vendor/autoload.php';

use App\Service\SwitchServiceCurl;
use App\Service\SwitchServiceGuzzle;

$switch = new SwitchServiceCurl();
$username = 'demo';
$password = 'demo';

// Login and get token from Switch Server
$result = $switch->login($username, $password);
$array = json_decode($result, true);
$token = $array['token'];

echo '<pre>';
echo 'Example with CURL:<br />';
var_dump($token);


$switch = new SwitchServiceGuzzle();
$username = 'demo';
$password = 'demo';

// Login and get token from Switch Server
$result = $switch->login($username, $password);
$array = json_decode($result, true);
$token = $array['token'];

echo '<pre>';
echo 'Example with GUZZLE:<br />';
var_dump($token);
