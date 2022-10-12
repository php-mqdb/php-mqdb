<?php

declare(strict_types=1);

/*
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Enumerator;

/**
 * Class Status.
 * List of possible status for a message.
 *  0: In queue (Waiting to be consumed)
 *  1: pending ack (waiting for response)
 *  2: ack received (treated, cannot be retreated)
 *  3: nack received (treated with non acknowledgment)
 *  4: ack not received (no response after a delay, can be deleted)
 *
 * @author Romain Cottard
 */
class Status
{
    /** @var int IN_QUEUE Message is in queue and can be consumed */
    const IN_QUEUE = 0;

    /** @var int ACK_PENDING Message has been consumed, but acknowledgement is pending */
    const ACK_PENDING = 1;

    /** @var int ACK_RECEIVED Message has been consumed and acknowledgement received */
    const ACK_RECEIVED = 2;

    /** @var int NACK_RECEIVED Message has been consumed and non acknowledgement received */
    const NACK_RECEIVED = 3;

    /** @var int ACK_NOT_RECEIVED Message has been consumed, but acknowledgement is pending since a long time */
    const ACK_NOT_RECEIVED = 4;
}
