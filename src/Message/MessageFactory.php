<?php

/**
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Message;

use PhpMqdb\Enumerator;

/**
 * Class Message
 *
 * @author  Romain Cottard
 */
class MessageFactory
{
    /**
     * Get new message instance.
     *
     * @param  string $contentType
     * @return MessageInterface
     */
    public static function create($contentType)
    {
        switch ($contentType) {
            case Enumerator\ContentType::JSON:
                return new MessageJson();
            case Enumerator\ContentType::TEXT:
            default:
                return new Message();
        }
    }
}
