<?php

/**
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Enumerator;

/**
 * Class Priority.
 * List of possible priority value for a message.
 *  1: Very High
 *  2: High
 *  3: Medium
 *  4: Low
 *  5: Very Low
 *
 * @author Romain Cottard
 */
class Priority
{
    /** @var int VERY_HIGH */
    const VERY_HIGH = 1;

    /** @var int HIGH */
    const HIGH = 2;

    /** @var int MEDIUM */
    const MEDIUM = 3;

    /** @var int LOW */
    const LOW = 4;

    /** @var int VERY_LOW */
    const VERY_LOW = 5;
}
