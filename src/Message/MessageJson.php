<?php

/**
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Message;

/**
 * Class Message
 *
 * @author  Romain Cottard
 */
class MessageJson extends Message
{
    /**
     * JsonMessage constructor.
     *
     * @param string $topic
     * @param mixed $content
     * @param bool $forceJsonEncode
     */
    public function __construct($topic = null, $content = null, $forceJsonEncode = true)
    {
        $dateNow = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->setDateCreate($dateNow->format('Y-m-d H:i:s'));
        $this->setDateAvailability($dateNow->format('Y-m-d H:i:s'));

        //~ Override type
        $this->setContentType('json');

        if ($topic !== null) {
            $this->setTopic($topic);
        }

        if ($content !== null) {
            if ($forceJsonEncode) {
                $content = json_encode($content);
            }
            $this->setContent($content);
        }
    }
}
