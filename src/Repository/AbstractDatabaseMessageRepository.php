<?php

/*
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMqdb\Repository;

use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Result;
use PhpMqdb\Enumerator;
use PhpMqdb\Exception\EmptySetValuesException;
use PhpMqdb\Exception\PhpMqdbConfigurationException;
use PhpMqdb\Exception\PhpMqdbException;
use PhpMqdb\Filter;
use PhpMqdb\Message;
use PhpMqdb\Message\MessageInterface;
use PhpMqdb\Query\QueryBuilder;
use PhpMqdb\Query\QueryBuilderFactory;
use Ramsey\Uuid\Uuid;

abstract class AbstractDatabaseMessageRepository implements MessageRepositoryInterface
{
    private Message\MessageFactoryInterface $messageFactory;
    private QueryBuilderFactory $queryBuilderFactory;

    /**
     * Run a query
     *
     * @param QueryBuilder $queryBuilder
     * @return \PDOStatement|Result
     */
    abstract protected function executeQuery(QueryBuilder $queryBuilder);

    /**
     * AbstractDatabaseMessageRepository constructor.
     *
     * @param QueryBuilderFactory $queryBuilderFactory
     */
    public function setQueryBuilderFactory(QueryBuilderFactory $queryBuilderFactory): void
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

        return \array_pop($messages);
    }

    /**
     * Get messages based on given context.
     *
     * @param Filter $filter
     * @return Message\MessageInterface[]
     * @throws PhpMqdbConfigurationException
     * @throws \Exception
     */
    public function getMessages(Filter $filter): array
    {
        $messages = [];

        $queryBuilder = $this->getQueryBuilder();

        try {
            $stmt    = $this->executeQuery($queryBuilder->buildQueryGet($filter, $this->protectMessages($filter)));
            $results = $stmt instanceof Result ? $stmt->fetchAllAssociative() : $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $exception) {
            throw new PhpMqdbException('Unable to fetch message!', (int) $exception->getCode(), $exception);
        }

        foreach ($results as $row) {
            $row     = (object) $row;
            $message = $this->messageFactory->create($row);

            $messages[] = $message;
        }

        return $messages;
    }

    /**
     * @param Filter $filter
     * @return int
     * @throws \Doctrine\DBAL\Exception
     * @throws PhpMqdbConfigurationException
     */
    public function countMessages(Filter $filter): int
    {
        $stmt = $this->executeQuery($this->getQueryBuilder()->buildQueryCount($filter));

        /** @var int|string $nbMessage */
        $nbMessage = ($stmt instanceof Result ? $stmt->fetchOne() : $stmt->fetch(\PDO::FETCH_COLUMN));
        return (int) $nbMessage;
    }

    /**
     * Publish message in queue.
     *
     * @param MessageInterface $message
     * @param bool $allowStatusUpdate Should status of messages be change on update or not (default false)
     * @return MessageRepositoryInterface
     * @throws EmptySetValuesException
     * @throws PhpMqdbConfigurationException
     * @throws \Exception
     */
    public function publishMessage(Message\MessageInterface $message, bool $allowStatusUpdate = false): MessageRepositoryInterface
    {
        $queryBuilder = $this->getQueryBuilder();

        $isNew = false;
        if (empty($message->getId())) {
            $message->setId($this->generateId());
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
     * @throws \Exception|Exception
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
        $stmt  = $this->executeQuery($this->getQueryBuilder()->buildQueryCountExisting($filterExisting));

        /** @var int|string $count */
        $count = $stmt instanceof Result ? $stmt->fetchOne() : $stmt->fetch(\PDO::FETCH_COLUMN);
        if ((int) $count > 0) {
            return $this;
        }

        return $this->publishMessage($message);
    }

    /**
     * @param \DateInterval $interval
     * @param int $bitmaskDelete
     * @return MessageRepositoryInterface
     * @throws \Exception
     */
    public function cleanMessages(
        \DateInterval $interval,
        int $bitmaskDelete = self::DELETE_SAFE
    ): MessageRepositoryInterface {
        $queryBuilder = $this->getQueryBuilder();
        $this->executeQuery($queryBuilder->buildQueryClean($bitmaskDelete, $this->getRelativeDate($interval)));

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
                \min($message->getPriority(), $existingMessage->getPriority())
            ) // We keep the highest priority (ie the lowest value)
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
     * @throws \Exception
     */
    private function protectMessages(Filter $filter): string
    {
        $pendingId = $this->generateShortId();

        $this->executeQuery($this->getQueryBuilder()->buildQueryProtect($filter, $pendingId));

        return $pendingId;
    }

    /**
     * Generate unique id.
     * Use uuid 7 for better performances with mysql db primary keys, due to ordered prefix uuid based on time
     *
     * @return string
     * @throws \Exception
     */
    private function generateId(): string
    {
        return Uuid::uuid7()->toString();
    }

    /**
     * Generate unique short id.
     *
     * @return string
     * @throws \Exception
     */
    private function generateShortId(): string
    {
        return sprintf('%08x', random_int(0, 0xffffffff));
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
