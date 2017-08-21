<?php

/**
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Enumerator;

/**
 * Class Status.
 * List of possible status for a message.
 *  text: message content without specification format (default)
 *  json: message content with json formatting
 *
 * @author Romain Cottard
 */
class ContentType
{
    /** @var string TEXT Message content is a text (default value) */
    const TEXT = 'text';

    /** @var string JSON Message content is in json format */
    const JSON = 'json';
}
