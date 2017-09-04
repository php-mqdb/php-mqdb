<?php


/**
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Repository;

use PhpMqdb\Filter;
use PhpMqdb\Message\MessageInterface;
use PhpMqdb\Exception\EmptySetValuesException;

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
     * @return $this
     */
    public function ack($id);

    /**
     * Send non-acknowledgement to the server.
     *
     * @param  string $id
     * @param  bool $requeue
     * @return $this
     */
    public function nack($id, $requeue = true);

    /**
     * Get message based on given context.
     *
     * @param  Filter $filter
     * @return MessageInterface
     */
    public function getMessage(Filter $filter);

    /**
     * Get messages based on given context.
     *
     * @param  Filter $filter
     * @return MessageInterface[]
     */
    public function getMessages(Filter $filter);

    /**
     * Publish message in queue.
     *
     * @param  MessageInterface $message
     * @return $this
     */
    public function publishMessage(MessageInterface $message);

    /**
     * Clean pending message with date update above given interval.
     *
     * @param  \DateInterval $interval
     * @return $this
     */
    public function cleanPendingMessages(\DateInterval $interval);

    /**
     * Clean message were done (ack / nack received)
     *
     * @param  \DateInterval $interval
     * @param  int $bitmaskDelete
     * @return $this
     * @throws \LogicException
     */
    public function cleanMessages(\DateInterval $interval, $bitmaskDelete = self::DELETE_SAFE);

    /**
     * Override fields names.
     *
     * @param  string[] $fields
     * @return $this
     */
    public function setFields(array $fields);

    /**
     * Override table name.
     *
     * @param  string $table
     * @return $this
     * @throws EmptySetValuesException
     * @throws \LogicException
     */
    public function setTable($table);

}
