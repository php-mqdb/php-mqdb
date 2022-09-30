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
use PhpMqdb\Enumerator;
use PhpMqdb\Message;
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

$date = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

//~ Publish messages


$index   = 1;
$content = new \stdClass();
$content->id     = (string) $index;
$content->title  = 'Content title #' . $index;
$content->filter = 1; //~ Bitmask

$message = new Message\Message();
$message->setPriority(Enumerator\Priority::MEDIUM);
$message->setTopic('publish.content');
$message->setStatus(Enumerator\Status::IN_QUEUE);
$message->setContent(json_encode($content));
$message->setContentType('json');
$message->setEntityId($content->id);
$message->setDateAvailability($date->format('Y-m-d H:i:s'));
$message->setDateCreate($date->format('Y-m-d H:i:s'));

$client->publish($message);


$content->filter = 2; // Update bitmask en new message
$messageUpdated  = clone $message;
$messageUpdated->setContent(json_encode($content));
$client->publishOrUpdateEntityMessage($messageUpdated);

$contentUpdated = json_decode($messageUpdated->getContent());
echo 'Message should have content with filter value "2": ' . $contentUpdated->filter . PHP_EOL;

$callbackMerge = function(Message\MessageInterface $existingMessage, Message\MessageInterface $message)
{
    $oldContent = json_decode($existingMessage->getContent());
    $newContent = json_decode($message->getContent());

    //~ Merge content
    $newContent->filter = ($newContent->filter | $oldContent->filter);
    $message->setContent(json_encode($newContent));

    $message->getContent();
};

$content->filter = 1; // Update bitmask en new message
$messageUpdated  = clone $message;
$messageUpdated->setContent(json_encode($content));
$client->publishOrUpdateEntityMessage($messageUpdated, $callbackMerge);

$contentUpdated = json_decode($messageUpdated->getContent());
echo 'Message should have content with filter value "3": ' . $contentUpdated->filter . PHP_EOL;


echo PHP_EOL;
