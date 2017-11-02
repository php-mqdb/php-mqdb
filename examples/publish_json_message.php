<?php

/*
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Examples;

use PhpMqdb\Client;
use PhpMqdb\Message;
use PhpMqdb\Enumerator;
use PhpMqdb\Repository\PDOMessageRepository;

require_once __DIR__ . '/../vendor/autoload.php';
$dbConf = require_once __DIR__ . '/config.php';

//~ Connection
$connection = new \PDO($dbConf->dsn, $dbConf->user, $dbConf->pass, $dbConf->opts);
$connection->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

//~ Repository
$repository = new PDOMessageRepository($connection);

//~ Client
$client = new Client($repository);

$date = new \DateTimeImmutable();

//~ Publish messages
for($index = 1; $index <= 1000; $index++) {
    echo 'process ' . $index . "\r";

    $interval = new \DateInterval('PT' . rand(0,10) . 'M' . rand(0, 59) . 'S');

    $content = new \stdClass();
    $content->id    = $index;
    $content->title = 'Content title #' . $index;

    $message = new Message\MessageJson('publish.content', $content);
    $message->setEntityId($content->id);
    $message->setPriority(rand(Enumerator\Priority::VERY_HIGH,Enumerator\Priority::VERY_LOW));
    $message->setDateAvailability($date->add($interval)->format('Y-m-d H:i:s'));

    $client->publish($message);
}

echo PHP_EOL;
