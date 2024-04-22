<?php

/*
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMqdb\Tests\Integration;

use Doctrine\DBAL\DriverManager;
use PhpMqdb\Client;
use PhpMqdb\Config\TableConfig;
use PhpMqdb\Enumerator\Priority;
use PhpMqdb\Enumerator\Status;
use PhpMqdb\Filter;
use PhpMqdb\Message\MessageFactory;
use PhpMqdb\Message\MessageInterface;
use PhpMqdb\Message\MessageJson;
use PhpMqdb\Query\QueryBuilderFactory;
use PhpMqdb\Repository\DBALMessageRepository;
use PhpMqdb\Repository\MessageRepositoryInterface;
use PHPUnit\Framework\TestCase;

class DBALRepositoryIntegrationTest extends TestCase
{
    use ConfigTrait;

    private Client $client;

    protected function setUp(): void
    {
        $config     = $this->getDBALConfig();
        $connection = DriverManager::getConnection($config);

        //~ Repository
        $repository = new DBALMessageRepository($connection);

        //~ Table Config
        $tableConfig = new TableConfig();

        //~ Factories
        $messageFactory      = new MessageFactory($tableConfig);
        $queryBuilderFactory = new QueryBuilderFactory($tableConfig);

        //~ Repository factories
        $repository->setMessageFactory($messageFactory);
        $repository->setQueryBuilderFactory($queryBuilderFactory);

        //~ Client
        $this->client = new Client($repository);
    }

    /**
     * @throws \Exception
     */
    public function testICanPublishSomeJsonMessages(): void
    {
        $this->client->cleanMessages(new \DateInterval('PT0S'), MessageRepositoryInterface::DELETE_ALL);

        //~ Publish messages
        for ($index = 1; $index <= 10; $index++) {
            $content        = new \stdClass();
            $content->id    = (string) $index;
            $content->title = 'Content title #' . $index;

            $message = new MessageJson('publish.content', $content);
            $message->setEntityId($content->id);
            $message->setPriority(rand(Priority::VERY_HIGH, Priority::VERY_LOW));

            $this->client->publish($message);
        }

        $filter = (new Filter())->setStatuses([Status::IN_QUEUE]);

        $this->assertSame(10, $this->client->countMessages($filter));
    }

    public function testICanGetOneMessageAndAcknowledgeItAndCleanIt(): void
    {
        //~ Get messages
        $message = $this->client->getMessage(new Filter());

        //~ Assert is a message and 9 message remaining in queue (10 - 1 pending)
        $this->assertInstanceOf(MessageInterface::class, $message);
        $this->assertSame(9, $this->client->countMessages(new Filter()));

        //~ Acknowledge the message
        $this->client->ack($message->getId());

        //~ Assert I have 1 message acknowledged in queue
        $this->assertSame(1, $this->client->countMessages((new Filter())->setStatuses([Status::ACK_RECEIVED])));

        //~ Clean acknowledged message and assert I have not anymore acknowledged message in queue
        $this->client->cleanMessages(new \DateInterval('PT0S'));
        $this->assertSame(0, $this->client->countMessages((new Filter())->setStatuses([Status::ACK_RECEIVED])));
    }

    public function testICanGetMultipleMessageAndAcknowledgeThemAndCleanIt(): void
    {
        //~ Get 5 messages
        $messages = $this->client->getMessages((new Filter())->setLimit(5));

        //~ Assert I have 5 messages and 4 messages remaining in queue (10 - 1 previously clean - 5 pending)
        $this->assertCount(5, $messages);
        $this->assertSame(4, $this->client->countMessages(new Filter()));

        //~ Acknowledge the messages
        foreach ($messages as $message) {
            $this->client->ack($message->getId());
        }

        //~ Assert I have 5 messages acknowledged in queue
        $this->assertSame(5, $this->client->countMessages((new Filter())->setStatuses([Status::ACK_RECEIVED])));

        //~ Clean acknowledged messages and assert I have not anymore acknowledged messages in queue
        $this->client->cleanMessages(new \DateInterval('PT0S'));
        $this->assertSame(0, $this->client->countMessages((new Filter())->setStatuses([Status::ACK_RECEIVED])));
    }

    public function testICanGetMultipleMessageAndDenyThemAndCleanIt(): void
    {
        //~ Get messages
        $messages = $this->client->getMessages((new Filter())->setLimit(5));

        //~ Assert I have 4 messages and 0 messages remaining in queue (10 - 6 previously clean - 4 pending)
        $this->assertCount(4, $messages);
        $this->assertSame(0, $this->client->countMessages(new Filter()));

        //~ Deny the messages (without requeue)
        foreach ($messages as $message) {
            $this->client->nack($message->getId(), false);
        }

        //~ Assert I have 4 messages denied in queue
        $this->assertSame(4, $this->client->countMessages((new Filter())->setStatuses([Status::NACK_RECEIVED])));

        //~ Clean denied messages and assert I have not anymore denied messages in queue
        $this->client->cleanMessages(new \DateInterval('PT0S'));
        $this->assertSame(0, $this->client->countMessages((new Filter())->setStatuses([Status::NACK_RECEIVED])));
    }
}
