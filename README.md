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
