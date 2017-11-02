# php-mq
PHP Message Queue. A full client / server to produce &amp; consume message from a queue.

## Installation
### Composer

```bash
$ composer require php-mqdb/php-mqdb
```

### Database
You must have a database with given table to store messages (the messages queue).
An sample create table is provide in sql/ directory.


## Getting messages - How to work
### Default ordering
Functions getMessage() & getMessages get messages in following order:
 - priority ascendant
 - date availability ascendant
 - date create ascendant

So, the messages with high priority, and with prior date availability (and with prior creation date)
will be get in priority.

### Filtering
You may filter messages you want to get. Possible filters are:
 - topic:  specify which topic you want (default: null). You can use `*` character in topic filter (like: publish.*)
 - statuses: Get messages with given status(es) (default get pending messages)
 - priorities: Get messages with only given priority(ies) (default: no filtering)
 - entity id: Filter only on the given entity id (default: no filtering)

### Filtering on date
By default, the system only gets the available and non-expired messages.
You can change filter value for available date & expiration date. You also can change the current date.

### Limit result
By default, getMessage() only gets one message.
You may get multiple messages at the same time by using getMessages(), but you need to set how much messages you want (default is 1)


## Usage
### Get a message

```php
<?php

use PhpMqdb\Client;
use PhpMqdb\Filter;
use PhpMqdb\Repository\PDOMessageRepository;

//~ Client
$client = new Client(new PDOMessageRepository(new \PDO([...])));


//~ Get a message
$message = $client->getMessage(new Filter());

//~ Treat message
echo 'Message: ' . var_export($message, true) . PHP_EOL;

//~ Send acknowledgement
$client->ack($message->getId());

//~ Get a message & send non-acknowledgement with requeue (set message status to "in queue")
//~ Message can be re-processed
$message = $client->getMessage(new Filter());
echo 'Message: ' . var_export($message, true) . PHP_EOL;
$client->nack($message->getId(), true);

//~ Get a message & send non-acknowledgement with no-requeue (set message status to "non-acknowledgement received")
$message = $client->getMessage(new Filter());
echo 'Message: ' . var_export($message, true) . PHP_EOL;
$client->nack($message->getId(), false);
```


### Get multiple messages

```php
<?php

use PhpMqdb\Client;
use PhpMqdb\Filter;
use PhpMqdb\Repository\PDOMessageRepository;

//~ Client
$client = new Client(new PDOMessageRepository(new \PDO([...])));

//~ Get a message
$messages = $client->getMessages(new Filter());

//~ Treat messages
foreach ($messages as $message) {
    echo 'Message: ' . var_export($message, true) . PHP_EOL;
    $client->ack($message->getId());
}
```


### Publish a message

```php
<?php

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

//~ Client
$client = new Client(new PDOMessageRepository(new \PDO([...])));

//~ Message content
$content        = new \stdClass();
$content->id    = 1;
$content->title = 'Content title #1';

//~ Build message
$message = new Message\Message();
$message->setPriority(Priority::MEDIUM);
$message->setTopic('publish.content');
$message->setStatus(Status::IN_QUEUE);
$message->setContent(json_encode($content));
$message->setContentType('json');
$message->setEntityId($content->id);
$message->setDateAvailability('2017-01-01 00:00:00');
$message->setDateCreate(date('Y-m-d H:i:s'));

//~ Publish the message (store in queue)
$client->publish($message);
```

### Use standard json message (with default values)

```php
<?php

use PhpMqdb\Client;
use PhpMqdb\Message;
use PhpMqdb\Repository\PDOMessageRepository;

require_once __DIR__ . '/../vendor/autoload.php';
$dbConf = require_once __DIR__ . '/config.php';

//~ Connection
$connection = new \PDO($dbConf->dsn, $dbConf->user, $dbConf->pass, $dbConf->opts);
$connection->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

//~ Client
$client = new Client(new PDOMessageRepository(new \PDO([...])));

//~ Message content
$content        = new \stdClass();
$content->id    = 1;
$content->title = 'Content title #1';

//~ Build message
$message = new Message\MessageJson('publish.content', $content);
$message->setEntityId($content->id);

//~ Publish the message (store in queue)
$client->publish($message);
```
