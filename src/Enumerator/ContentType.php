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
 * List of possible content type for a message.
 *  text: message content without specification format (default)
 *  json: message content with json formatting
 */
class ContentType
{
    /** @var string TEXT Message content is a text (default value) */
    public const TEXT = 'text';

    /** @var string JSON Message content is in json format */
    public const JSON = 'json';
}
