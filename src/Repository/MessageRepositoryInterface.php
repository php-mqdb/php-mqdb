<?php declare(strict_types=1);

/*
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Repository;

use PhpMqdb\Exception\EmptySetValuesException;
use PhpMqdb\Filter;
use PhpMqdb\Message\MessageInterface;

/**
 * Interface for Message Repository
 *
 * @author Romain Cottard
 */
interface MessageRepositoryInterface
{
    /** @var int DELETE_ACK_RECEIVED Bit value to delete message with status Enumerator\Status::ACK_RECEIVED */
    const DELETE_ACK_RECEIVED = 0x1;

    /** @var int DELETE_NACK_RECEIVED Bit value to delete message with status Enumerator\Status::NACK_RECEIVED */
    const DELETE_NACK_RECEIVED = 0x2;

    /** @var int DELETE_ACK_NOT_RECEIVED Bit value to delete message with status Enumerator\Status::ACK_NOT_RECEIVED */
    const DELETE_ACK_NOT_RECEIVED = 0x4;

    /** @var int DELETE_ACK_PENDING Bit value to delete message with status Enumerator\Status::ACK_PENDING */
    const DELETE_ACK_PENDING = 0x8;

    /** @var int DELETE_SAFE Bitmask value to delete message with status Enumerator\Status::ACK_RECEIVED, Enumerator\Status::NACK_RECEIVED & Enumerator\Status::ACK_NOT_RECEIVED */
    const DELETE_SAFE = 0x7;

    /** @var int DELETE_ALL Bitmask include all previous bit values (Should be delete all messages) */
    const DELETE_ALL = 0xff;

    /**
     * Send acknowledgement to the server.
     *
     * @param  string $id
     * @return MessageRepositoryInterface
     */
    public function ack(string $id): MessageRepositoryInterface;

    /**
     * Send non-acknowledgement to the server.
     *
     * @param  string $id
     * @param  bool $requeue
     * @return MessageRepositoryInterface
     */
    public function nack(string $id, bool $requeue = true): MessageRepositoryInterface;

    /**
     * Get message based on given context.
     *
     * @param  Filter $filter
     * @return MessageInterface
     */
    public function getMessage(Filter $filter): ?MessageInterface;

    /**
     * Get messages based on given context.
     *
     * @param  Filter $filter
     * @return MessageInterface[]
     */
    public function getMessages(Filter $filter): iterable;

    /**
     * Count messages based on given context
     *
     * @param Filter $filter
     * @return int
     */
    public function countMessages(Filter $filter): int;

    /**
     * Publish message in queue.
     *
     * @param  MessageInterface $message
     * @param  bool $allowStatusUpdate Should status of messages be change on update or not (default false)
     * @return $this
     */
    public function publishMessage(MessageInterface $message, bool $allowStatusUpdate = false): MessageRepositoryInterface;

    /**
     * Publish message, or update if there is already a message for the same entity_id in queue
     *  Check Client::publishOrUpdateEntityMessage documentation for important notes about usage
     *
     * @param MessageInterface $message
     * @return mixed
     */
    public function publishOrUpdateEntityMessage(MessageInterface $message);

    /**
     * Clean pending messages with date update above given interval.
     *
     * @param  \DateInterval $interval
     * @return MessageRepositoryInterface
     */
    public function cleanPendingMessages(\DateInterval $interval): MessageRepositoryInterface;

    /**
     * Reset pending messages with date update above given interval.
     *
     * @param  \DateInterval $interval
     * @return MessageRepositoryInterface
     */
    public function ResetPendingMessages(\DateInterval $interval): MessageRepositoryInterface;

    /**
     * Clean done messages (ack / nack received)
     *
     * @param  \DateInterval $interval
     * @param  int $bitmaskDelete
     * @return MessageRepositoryInterface
     * @throws \LogicException
     */
    public function cleanMessages(\DateInterval $interval, int $bitmaskDelete = self::DELETE_SAFE): MessageRepositoryInterface;

}
