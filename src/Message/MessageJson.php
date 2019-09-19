<?php declare(strict_types=1);

/*
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
     * MessageJson constructor.
     *
     * @param string|null $topic
     * @param null $content
     * @param bool $forceJsonEncode
     * @throws \Exception
     */
    public function __construct(string $topic = null, $content = null, bool $forceJsonEncode = true)
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
            $this->setContent((string) $content);
        }
    }
}
