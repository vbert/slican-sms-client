<?php
declare(strict_types=1);

namespace Vbert\SmsClient;

require '../vendor/autoload.php';

use \Vbert\SmsClient\SmsClient;
use \Vbert\SmsClient\Adapters\Slican;

$testConfig = array(
    'host' => '94.254.244.164',
    'port' => 5524,
    'pinSimCard' => 1941
);

$testMessage = array(
    'to' => '502740930',
    'content' => 'Test sms from Slican IPU-14 ('. date('H:i:s') .')'
);

$slicanAdapter = new Slican($testConfig);
$client = new SmsClient($slicanAdapter);
$ok = $client->send($testMessage);

var_dump([
    'Adapter name' => $slicanAdapter->getAdapter(),
    'Send OK' => $ok
]);
