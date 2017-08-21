<?php

/**
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Repository;

use PhpMqdb\Message;

/**
 * Interface for Message Repository
 *
 * @author Romain Cottard
 */
class PDOMessageRepository extends AbstractDatabaseMessageRepository
{
    /**
     * AbstractDatabaseMessageRepository constructor.
     *
     * @param \PDO $connection
     * @param string $classFactory
     */
    public function __construct(\PDO $connection, $classFactory = Message\MessageFactory::class)
    {
        $this->setConnector($connection);
        $this->setClassMessageFactory($classFactory);
    }
}
