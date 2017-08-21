<?php

/**
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Repository;

use Doctrine\DBAL\Connection;
use PhpMqdb\Message;

/**
 * Interface for Message Repository
 *
 * @author Romain Cottard
 */
class DBALMessageRepository extends AbstractDatabaseMessageRepository
{
    /**
     * AbstractDatabaseMessageRepository constructor.
     *
     * @param Connection $connection
     * @param string $classFactory
     */
    public function __construct(Connection $connection, $classFactory = Message\MessageFactory::class)
    {
        $this->setConnector($connection);
        $this->setClassMessageFactory($classFactory);
    }
}
