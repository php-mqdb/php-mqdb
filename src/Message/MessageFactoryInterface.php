<?php declare(strict_types=1);

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Message;

/**
 * Class MessageFactory
 *
 * @author Romain Cottard
 */
interface MessageFactoryInterface
{
    /**
     * @param \stdClass|null $data
     * @return MessageInterface
     * @throws \Exception
     */
    public function create(\stdClass $data = null): MessageInterface;
}
