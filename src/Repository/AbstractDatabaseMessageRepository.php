<?php declare(strict_types=1);

/*
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Repository;

use Doctrine\DBAL\Driver\Statement;
use PhpMqdb\Enumerator;
use PhpMqdb\Exception\EmptySetValuesException;
use PhpMqdb\Exception\PhpMqdbConfigurationException;
use PhpMqdb\Filter;
use PhpMqdb\Message;
use PhpMqdb\Message\MessageInterface;
use PhpMqdb\Query\QueryBuilder;
use PhpMqdb\Query\QueryBuilderFactory;

/**
 * Interface for Message Repository
 *
 * @author Romain Cottard
 */
abstract class AbstractDatabaseMessageRepository implements MessageRepositoryInterface
{
    /** @var Message\MessageFactoryInterface $messageFactory */
    private $messageFactory;

    /** @var QueryBuilderFactory $queryBuilderFactory */
    private $queryBuilderFactory;

    /**
     * Run a query
     *
     * @param QueryBuilder $queryBuilder
     * @return \PDOStatement|Statement
     */
    abstract protected function executeQuery(QueryBuilder $queryBuilder);

    /**
     * AbstractDatabaseMessageRepository constructor.
     *
     * @param QueryBuilderFactory $queryBuilderFactory
     */
    public function setQueryBuilderFactory(QueryBuilderFactory $queryBuilderFactory)
    {
        $this->queryBuilderFactory = $queryBuilderFactory;
    }

    /**
     * Set message factory instance.
     *
     * @param Message\MessageFactoryInterface $messageFactory
     * @return $this
     * @throws \LogicException
     */
    public function setMessageFactory(Message\MessageFactoryInterface $messageFactory): MessageRepositoryInterface
    {
        $this->messageFactory = $messageFactory;

        return $this;
    }

    /**
     * Send acknowledgement to the server.
     *
     * @param string $id
     * @return MessageRepositoryInterface
     * @throws \Exception
     */
    public function ack(string $id): MessageRepositoryInterface
    {
        $queryBuilder = $this->getQueryBuilder();

        $this->executeQuery($queryBuilder->buildQueryUpdate($id, Enumerator\Status::ACK_RECEIVED));

        return $this;
    }

    /**
     * Send non-acknowledgement to the server.
     *
     * @param string $id
     * @param bool $requeue
     * @return $this
     * @throws \Exception
     */
    public function nack(string $id, bool $requeue = true): MessageRepositoryInterface
    {
        $status = Enumerator\Status::NACK_RECEIVED;

        if ($requeue) {
            $status = Enumerator\Status::IN_QUEUE;
        }

        $queryBuilder = $this->getQueryBuilder();

        $this->executeQuery($queryBuilder->buildQueryUpdate($id, $status));

        return $this;
    }

    /**
     * Get message based on given context.
     *
     * @param Filter $filter
     * @return Message\MessageInterface|null
     * @throws PhpMqdbConfigurationException
     */
    public function getMessage(Filter $filter): ?Message\MessageInterface
    {
        //~ override filter limit to 1
        $filter->setLimit(1);

        $messages = $this->getMessages($filter);

        if (empty($messages)) {
            return null;
        }

        return array_pop($messages);
    }

    /**
     * Get messages based on given context.
     *
     * @param Filter $filter
     * @return Message\MessageInterface[]
     * @throws PhpMqdbConfigurationException
     * @throws \Exception
     */
    public function getMessages(Filter $filter): iterable
    {
        $messages = [];

        $queryBuilder = $this->getQueryBuilder();

        $stmt = $this->executeQuery($queryBuilder->buildQueryGet($filter, $this->protectMessages($filter)));

        while (null != ($row = $stmt->fetch(\PDO::FETCH_OBJ))) {

            $message = $this->messageFactory->create($row);

            $messages[] = $message;
        }

        return $messages;
    }

    /**
     * @param Filter $filter
     * @return int
     * @throws PhpMqdbConfigurationException
     */
    public function countMessages(Filter $filter): int
    {
        $stmt = $this->executeQuery($this->getQueryBuilder()->buildQueryCount($filter));

        return (int) $stmt->fetchColumn();
    }

    /**
     * Publish message in queue.
     *
     * @param  MessageInterface $message
     * @param  bool $allowStatusUpdate Should status of messages be change on update or not (default false)
     * @return MessageRepositoryInterface
     * @throws EmptySetValuesException
     * @throws PhpMqdbConfigurationException
     */
    public function publishMessage(Message\MessageInterface $message, bool $allowStatusUpdate = false): MessageRepositoryInterface
    {
        $queryBuilder = $this->getQueryBuilder();

        $isNew = false;
        if (empty($message->getId())) {
            $message->setId($this->generateId(4));
            $isNew = true;
        }

        $this->executeQuery($queryBuilder->buildQueryPublish($message, $isNew, $allowStatusUpdate));

        return $this;
    }

    /**
     * Publish message, or update if there is already a message for the same entity_id in queue
     *  Check Client::publishOrUpdateEntityMessage documentation for important notes about usage
     *
     * @param MessageInterface $message
     * @param callable|null $mergeCallback
     * @return MessageRepositoryInterface
     * @throws EmptySetValuesException
     * @throws PhpMqdbConfigurationException
     * @throws \Exception
     */
    public function publishOrUpdateEntityMessage(
        Message\MessageInterface $message,
        ?callable $mergeCallback = null
    ): MessageRepositoryInterface {
        if (empty($message->getEntityId())) {
            throw new \LogicException("Can't use publishOrUpdateEntityMessage if there is not Entity in the message");
        }

        $filterExisting = new Filter();
        $filterExisting->setEntityId($message->getEntityId())
            ->setTopic($message->getTopic())
        ;
        $existingMessage = $this->getMessage($filterExisting);

        if ($existingMessage instanceof Message\MessageInterface) {
            $this->mergeMessages($existingMessage, $message);

            if ($mergeCallback !== null) {
                $mergeCallback($existingMessage, $message);
            }
        }

        return $this->publishMessage($message, true);
    }

    /**
     * Publish message, or update if there is already a message for the same entity_id in queue
     *
     * @param MessageInterface $message
     * @return MessageRepositoryInterface
     * @throws EmptySetValuesException
     * @throws PhpMqdbConfigurationException
     * @throws \Exception
     */
    public function publishOrSkipEntityMessage(Message\MessageInterface $message): MessageRepositoryInterface
    {
        if (empty($message->getEntityId())) {
            throw new \LogicException("Can't use publishOrSkipEntityMessage if there is not Entity in the message");
        }

        $filterExisting = new Filter();
        $filterExisting->setEntityId($message->getEntityId())->setTopic($message->getTopic());
        $stmt = $this->executeQuery($this->getQueryBuilder()->buildQueryCountExisting($filterExisting));
        $count = (int) $stmt->fetchColumn();
        if ($count > 0) {
            return $this;
        }

        return $this->publishMessage($message);
    }

    /**
     * @param \DateInterval $interval
     * @param int $deleteBitmask
     * @return MessageRepositoryInterface
     * @throws \Exception
     */
    public function cleanMessages(
        \DateInterval $interval,
        int $deleteBitmask = self::DELETE_SAFE
    ): MessageRepositoryInterface {
        $queryBuilder = $this->getQueryBuilder();
        $this->executeQuery($queryBuilder->buildQueryClean($deleteBitmask, $this->getRelativeDate($interval)));

        return $this;
    }

    /**
     * @param \DateInterval $interval
     * @return MessageRepositoryInterface
     * @throws \Exception
     */
    public function cleanPendingMessages(\DateInterval $interval): MessageRepositoryInterface
    {
        $queryBuilder = $this->getQueryBuilder();
        $this->executeQuery($queryBuilder->buildQueryCleanPending($this->getRelativeDate($interval)));

        return $this;
    }

    /**
     * @param \DateInterval $interval
     * @return MessageRepositoryInterface
     * @throws \Exception
     */
    public function resetPendingMessages(\DateInterval $interval): MessageRepositoryInterface
    {
        $queryBuilder = $this->getQueryBuilder();
        $this->executeQuery($queryBuilder->buildQueryResetPending($this->getRelativeDate($interval)));

        return $this;
    }

    /**
     * @param MessageInterface $existingMessage
     * @param MessageInterface $message
     * @return MessageRepositoryInterface
     * @throws \Exception
     */
    protected function mergeMessages(
        Message\MessageInterface $existingMessage,
        Message\MessageInterface $message
    ): MessageRepositoryInterface {
        // Update message using existing date when needed
        $message->setId($existingMessage->getId())
            ->setStatus(Enumerator\Status::IN_QUEUE)
            ->setDateCreate($existingMessage->getDateCreate())
            ->setDateAvailability($existingMessage->getDateAvailability()) // Keep previous availability
            ->setPriority(
                min($message->getPriority(), $existingMessage->getPriority())
            ) // We keep the highest priority (ie lowest value)
            ->setDateUpdate($this->getRelativeDate())
        ;

        return $this;
    }

    /**
     * Protect message from double consuming in parallels scripts.
     *
     * @param Filter $filter
     * @return string
     * @throws PhpMqdbConfigurationException
     */
    private function protectMessages(Filter $filter): string
    {
        $pendingId = $this->generateId(1);

        $this->executeQuery($this->getQueryBuilder()->buildQueryProtect($filter, $pendingId));

        return $pendingId;
    }

    /**
     * Generate unique id. Format is: [0-f]{16}-[0-f]{16}-...
     *
     * @param int $nbChunk Number of "chunk" of 8 hexadecimal chars in generated id.
     * @return string
     * @throws \Exception
     */
    private function generateId(int $nbChunk = 4): string
    {
        $chunks = [];
        for ($i = 0; $i < $nbChunk; $i++) {
            $chunks[] = sprintf('%08x', random_int(0, 0xffffffff));
        }

        return implode('-', $chunks);
    }

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilderFactory->getBuilder();
    }

    /**
     * @param \DateInterval|null $interval
     * @return string
     * @throws \Exception
     */
    private function getRelativeDate(\DateInterval $interval = null): string
    {
        $date = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        if ($interval !== null) {
            $date = $date->sub($interval);
        }

        return $date->format('Y-m-d H:i:s');
    }
}
