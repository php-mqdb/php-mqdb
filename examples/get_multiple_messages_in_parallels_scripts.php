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

error_reporting(E_ALL);

const NUMBER_OF_WORKER = 50;
const NUMBER_OF_MESSAGES = 1000;

$time = -microtime(true);

for ($childIndex = 0; $childIndex < NUMBER_OF_WORKER; $childIndex++) {
    $pid = \pcntl_fork();

    if ($pid === -1 || $pid === 0) {
        goto child;
    }
}

if ($pid === -1) {
    throw new \Exception('Cannot duplicate this process.');
}

//~ Parent process
if ($pid > 0) {

    while (pcntl_waitpid(0, $status) !== -1) {
        $status = pcntl_wexitstatus($status);
        echo "Child of $pid completed (status: $status)\n";
    }

    $time += microtime(true);
    echo "Total Time taken: " . round($time, 5) . "s" . PHP_EOL;
    exit(0);
}

exit(0);

//~ Child Process
child:

require_once __DIR__ . '/../vendor/autoload.php';
$repository = require_once __DIR__ . '/_init_pdo_repository.php';

//~ Table Config
$tableConfig = new TableConfig();
$tableConfig->setFields(
    [
        'id'                => 'message_id',
        'status'            => 'message_status',
        'priority'          => 'message_priority',
        'topic'             => 'message_topic',
        'content'           => 'message_content',
        'content_type'      => 'message_content_type',
        'pending_id'        => 'message_pending_id',
        'date_create'       => 'message_date_create',
        'date_update'       => 'message_date_update',
    ]
);

$tableConfig->setOrders(['priority' => 'ASC']);

//~ Factories
$messageFactory      = new MessageFactory($tableConfig);
$queryBuilderFactory = new QueryBuilderFactory($tableConfig);

//~ Repository factories
$repository->setMessageFactory($messageFactory);
$repository->setQueryBuilderFactory($queryBuilderFactory);

//~ Client
$client = new Client($repository);

//~ Filter
$filter = (new Filter())
    ->setPriorities([3])
    ->setLimit(NUMBER_OF_MESSAGES)
;

//~ Get messages
$messages = $client->getMessages($filter);

foreach ($messages as $message) {
    if (!isset($messageList[$message->getId()])) {
        $messageList[$message->getId()] = 0;
    }
    $messageList[$message->getId()]++;
    $client->ack($message->getId());
    $content = json_decode($message->getContent());

    echo $content->title . " from child #$childIndex" . PHP_EOL;
}

$time += microtime(true);
echo "Time taken: " . round($time, 5) . PHP_EOL;
