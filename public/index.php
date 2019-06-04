<?php

namespace App;

require __DIR__.'/../vendor/autoload.php';

use App\Service\SwitchService;

$switch = new SwitchService();
$username = 'demo';
$password = 'demo';

// Login and get token from Switch Server
$result = $switch->login($username, $password);
$array = json_decode($result, true);
$token = $array['token'];


