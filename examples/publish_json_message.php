<?php

/*
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMqdb\Examples;

use PhpMqdb\Client;
use PhpMqdb\Config\TableConfig;
use PhpMqdb\Message;
use PhpMqdb\Enumerator;
use PhpMqdb\Message\MessageFactory;
use PhpMqdb\Query\QueryBuilderFactory;

require_once __DIR__ . '/../vendor/autoload.php';
$repository = require_once __DIR__ . '/_init_pdo_repository.php';

//~ Table Config
$tableConfig = new TableConfig();

//~ Factories
$messageFactory      = new MessageFactory($tableConfig);
$queryBuilderFactory = new QueryBuilderFactory($tableConfig);

//~ Repository factories
$repository->setMessageFactory($messageFactory);
$repository->setQueryBuilderFactory($queryBuilderFactory);

//~ Client
$client = new Client($repository);

$date = new \DateTimeImmutable();

//~ Publish messages
for($index = 1; $index <= 200000; $index++) {
    echo 'process ' . $index . "\r";

    $interval = new \DateInterval('PT' . rand(0,10) . 'M' . rand(0, 59) . 'S');

    $content = new \stdClass();
    $content->id    = (string) $index;
    $content->title = 'Content title #' . $index;

    $message = new Message\MessageJson('publish.content', $content);
    $message->setEntityId($content->id);
    $message->setPriority(rand(Enumerator\Priority::VERY_HIGH,Enumerator\Priority::VERY_LOW));
    $message->setDateAvailability($date->add($interval)->format('Y-m-d H:i:s'));

    $client->publish($message);
}

echo PHP_EOL;
