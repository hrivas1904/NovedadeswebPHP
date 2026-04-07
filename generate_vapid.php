<?php

require __DIR__ . '/vendor/autoload.php';

use Minishlink\WebPush\VAPID;

$keys = VAPID::createVapidKeys();

echo "Public Key:\n";
echo $keys['publicKey'];
echo "\n\n";

echo "Private Key:\n";
echo $keys['privateKey'];
echo "\n";  