<?php

/*
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb;

use PhpMqdb\Enumerator\Status;
use PhpMqdb\Repository\MessageRepositoryInterface;
use PhpMqdb\Message;

/**
 * Class Client
 *
 * @author Romain Cottard
 */
class Client
{
    /**
     * @var MessageRepositoryInterface $messageRepository
     */
    private $messageRepository = null;

    /**
     * Client constructor.
     *
     * @param MessageRepositoryInterface $messageRepository
     */
    public function __construct(MessageRepositoryInterface $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    /**
     * Send acknowledgement to the server.
     *
     * @param  string $id
     * @return $this
     */
    public function ack($id)
    {
        $this->messageRepository->ack($id);

        return $this;
    }

    /**
     * Send non-acknowledgement to the server.
     *
     * @param  string $id
     * @param  bool $requeue
     * @return $this
     */
    public function nack($id, $requeue = true)
    {
        $this->messageRepository->nack($id, $requeue);

        return $this;
    }

    /**
     * Get message based on given context.
     *
     * @param  Filter $filter
     * @return Message\MessageInterface
     */
    public function getMessage(Filter $filter)
    {
        return $this->messageRepository->getMessage($filter);
    }

    /**
     * Get messages based on given context.
     *
     * @param  Filter $filter
     * @return Message\MessageInterface[]
     */
    public function getMessages(Filter $filter)
    {
        return $this->messageRepository->getMessages($filter);
    }

    /**
     * Count messages based on given context
     *
     * @param Filter $filter
     * @return int
     */
    public function countMessages(Filter $filter)
    {
        return $this->messageRepository->countMessages($filter);
    }

    /**
     * Publish message in queue.
     *
     * @param  Message\MessageInterface $message
     * @return $this
     */
    public function publish(Message\MessageInterface $message)
    {
        $this->messageRepository->publishMessage($message);

        return $this;
    }

    /**
     * Publish message, or update if there is already a message for the same entity_id in queue
     *  Note 1: doesn't ensure there will never be duplicate messages (only to be used for performance when worker can be slow)
     *  Note 2: priority of the message will be the highest between existing and new message
     *  Note 3: message content is overwritten (should not be used when contents may be different)
     *
     * @param  Message\MessageInterface $message
     * @return $this
     */
    public function publishOrUpdateEntityMessage(Message\MessageInterface $message)
    {
        $this->messageRepository->publishOrUpdateEntityMessage($message);

        return $this;
    }

    /**
     * Clean pending message with date update above given interval.
     *
     * @param  \DateInterval $interval
     * @return $this
     */
    public function cleanPendingMessages(\DateInterval $interval)
    {
        $this->messageRepository->cleanPendingMessages($interval);

        return $this;
    }

    /**
     * Clean message were done (ack / nack received)
     *
     * @param  \DateInterval $interval
     * @param  int $bitmaskDelete
     * @return $this
     */
    public function cleanMessages(\DateInterval $interval, $bitmaskDelete = MessageRepositoryInterface::DELETE_SAFE)
    {
        $this->messageRepository->cleanMessages($interval, $bitmaskDelete);

        return $this;
    }
}
