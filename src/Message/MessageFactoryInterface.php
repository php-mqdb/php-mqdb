<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMqdb\Message;

interface MessageFactoryInterface
{
    /**
     * @param \stdClass|null $data
     * @return MessageInterface
     * @throws \Exception
     */
    public function create(\stdClass|null $data = null): MessageInterface;
}
