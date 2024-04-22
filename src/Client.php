<?php

/*
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMqdb;

use PhpMqdb\Repository\MessageRepositoryInterface;

class Client
{
    private MessageRepositoryInterface $messageRepository;

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
    public function ack(string $id): self
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
    public function nack(string $id, bool $requeue = true): self
    {
        $this->messageRepository->nack($id, $requeue);

        return $this;
    }

    /**
     * Get message based on given context.
     *
     * @param  Filter $filter
     * @return Message\MessageInterface|null
     */
    public function getMessage(Filter $filter): ?Message\MessageInterface
    {
        return $this->messageRepository->getMessage($filter);
    }

    /**
     * Get messages based on given context.
     *
     * @param  Filter $filter
     * @return Message\MessageInterface[]
     */
    public function getMessages(Filter $filter): array
    {
        return $this->messageRepository->getMessages($filter);
    }

    /**
     * Count messages based on given context
     *
     * @param Filter $filter
     * @return int
     */
    public function countMessages(Filter $filter): int
    {
        return $this->messageRepository->countMessages($filter);
    }

    /**
     * Publish message in queue.
     *
     * @param  Message\MessageInterface $message
     * @return $this
     */
    public function publish(Message\MessageInterface $message): self
    {
        $this->messageRepository->publishMessage($message);

        return $this;
    }

    /**
     * Publish message, or update if there is already a message for the same entity_id in queue
     *  Note 1: doesn't ensure there will never be duplicate messages (only to be used for performance when worker can be slow)
     *  Note 2: priority of the message will be the highest between existing and new message
     *  Note 3: message content is overwritten if no merge callback is passed (should not be used when contents may be different)
     *
     * @param Message\MessageInterface $message
     * @param callable|null $mergeCallback
     * @return $this
     * @throws Exception\EmptySetValuesException
     * @throws Exception\PhpMqdbConfigurationException
     */
    public function publishOrUpdateEntityMessage(
        Message\MessageInterface $message,
        ?callable $mergeCallback = null,
    ): self {
        $this->messageRepository->publishOrUpdateEntityMessage($message, $mergeCallback);

        return $this;
    }

    /**
     * Publish message, or skip if there is already a message for the same entity_id in queue or pending
     *
     * @param Message\MessageInterface $message
     * @return $this
     * @throws Exception\EmptySetValuesException
     * @throws Exception\PhpMqdbConfigurationException
     */
    public function publishOrSkipEntityMessage(Message\MessageInterface $message): self
    {
        $this->messageRepository->publishOrSkipEntityMessage($message);

        return $this;
    }

    /**
     * Clean pending messages with date update above given interval.
     * ie set them to NACK to tell you don't know if they have been consumed or not
     * to be used if you want to insure your message won't be consumed twice, even if it may not be consumed at all
     *
     * @param  \DateInterval $interval
     * @return $this
     */
    public function cleanPendingMessages(\DateInterval $interval): self
    {
        $this->messageRepository->cleanPendingMessages($interval);

        return $this;
    }

    /**
     * Reset pending messages with date update above given interval,
     * ie set them back to IN QUEUE to be consumed by a new worker in case they have not yet be consumed
     * to be used if you want to insure your message will be consumed, even if it may be done several times
     *
     * @param  \DateInterval $interval
     * @return $this
     */
    public function replayPendingMessages(\DateInterval $interval): self
    {
        $this->messageRepository->resetPendingMessages($interval);

        return $this;
    }

    /**
     * Clean done messages (ack / nack received)
     *
     * @param  \DateInterval $interval
     * @param  int $bitmaskDelete
     * @return $this
     */
    public function cleanMessages(
        \DateInterval $interval,
        int $bitmaskDelete = MessageRepositoryInterface::DELETE_SAFE,
    ): self {
        $this->messageRepository->cleanMessages($interval, $bitmaskDelete);

        return $this;
    }
}
