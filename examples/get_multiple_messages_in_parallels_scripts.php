<?php

/*
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Examples;

use PhpMqdb\Client;
use PhpMqdb\Filter;
use PhpMqdb\Repository\PDOMessageRepository;

$messageList = [];

for ($childIndex = 0; $childIndex < 10; $childIndex++) {
    $pid = pcntl_fork();

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
        echo "Child $status completed\n";
    }

    asort($messageList);
    var_export($messageList);
    exit(0);
}

exit(0);

//~ Child Process
child:
require_once __DIR__ . '/../vendor/autoload.php';
$dbConf = require_once __DIR__ . '/config.php';

//~ Connection
$connection = new \PDO($dbConf->dsn, $dbConf->user, $dbConf->pass, $dbConf->opts);
$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

//~ Repository
$repository = new PDOMessageRepository($connection);

//~ Client
$client = new Client($repository);

//~ Filter
$filter = (new Filter())->setLimit(20);

//~ Get messages
$messages = $client->getMessages($filter);

foreach ($messages as $message) {
    if (!isset($messageList[$message->getId()])) {
        $messageList[$message->getId()] = 0;
    }
    $messageList[$message->getId()]++;
    $client->ack($message->getId());
    $content = json_decode($message->getContent());

    echo $content->title . PHP_EOL;
}
