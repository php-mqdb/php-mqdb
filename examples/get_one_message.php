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
use PhpMqdb\Filter;
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

//~ New filter
$filter = new Filter();


//~ Get a message
$message = $client->getMessage($filter);

//~ Treat message
echo 'Message (& send acknowledgement): ' . var_export($message, true) . PHP_EOL;
echo 'Content:' . var_export($message->getContent(), true) . PHP_EOL;

//~ Send acknowledgement
$client->ack($message->getId());

//~ Get a message & send non-acknowledgement with requeue (set message status to "in queue")
//~ Message can be re-processed
$message = $client->getMessage($filter);
echo 'Message (& send non acknowledgement with requeue): ' . var_export($message, true) . PHP_EOL;
echo 'Content:' . var_export($message->getContent(), true) . PHP_EOL;
$client->nack($message->getId(), true);

//~ Get a message & send non-acknowledgement with no-requeue (set message status to "non-acknowledgement received")
$message = $client->getMessage($filter);
echo 'Message (& send non acknowledgement with no requeue): ' . var_export($message, true) . PHP_EOL;
echo 'Content:' . var_export($message->getContent(), true) . PHP_EOL;
$client->nack($message->getId(), false);
