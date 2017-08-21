<?php

/*
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Examples;

use PhpMqdb\Client;
use PhpMqdb\Enumerator\Priority;
use PhpMqdb\Enumerator\Status;
use PhpMqdb\Message;
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

//~ Publish messages
for($index = 1; $index <= 1000; $index++) {
    echo 'process ' . $index . "\r";

    $content = new \stdClass();
    $content->id    = $index;
    $content->title = 'Content title #' . $index;


    $message = new Message\Message();
    $message->setPriority(Priority::MEDIUM);
    $message->setTopic('publish.content');
    $message->setStatus(Status::IN_QUEUE);
    $message->setContent(json_encode($content));
    $message->setContentType('json');
    $message->setEntityId($content->id);
    $message->setDateAvailability('2017-01-01 00:00:00');
    $message->setDateCreate((new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s'));

    $client->publish($message);
}

echo PHP_EOL;
