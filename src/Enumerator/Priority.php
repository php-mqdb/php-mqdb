<?php

/*
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMqdb\Enumerator;

/**
 * List of possible priority value for a message.
 *  1: Very High
 *  2: High
 *  3: Medium
 *  4: Low
 *  5: Very Low
 */
class Priority
{
    /** @var int VERY_HIGH */
    public const VERY_HIGH = 1;

    /** @var int HIGH */
    public const HIGH = 2;

    /** @var int MEDIUM */
    public const MEDIUM = 3;

    /** @var int LOW */
    public const LOW = 4;

    /** @var int VERY_LOW */
    public const VERY_LOW = 5;
}
