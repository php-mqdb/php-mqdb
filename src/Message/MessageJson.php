<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMqdb\Message;

class MessageJson extends Message
{
    /**
     * MessageJson constructor.
     *
     * @param string|null $topic
     * @param mixed|null $content
     * @param bool $forceJsonEncode
     * @throws \Exception
     */
    public function __construct(string $topic = null, mixed $content = null, bool $forceJsonEncode = true)
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
                $content = \json_encode($content, flags: JSON_THROW_ON_ERROR);
            }

            if (\is_object($content) || \is_array($content)) {
                throw new \RuntimeException('Cannot set content, it is not a scalar value!');
            }
            /** @var string|int|float|null|bool $content */
            $this->setContent((string) $content);
        }
    }
}
