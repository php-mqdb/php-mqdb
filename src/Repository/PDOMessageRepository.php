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
    /** @var \PDO $connection */
    private $connection = null;

    /**
     * PDOMessageRepository constructor.
     *
     * @param \PDO $connection
     * @param string $classFactory
     */
    public function __construct(\PDO $connection, $classFactory = Message\MessageFactory::class)
    {
        $this->connection = $connection;
        $this->setClassMessageFactory($classFactory);
    }

    /**
     * @param string $query
     * @return \PDOStatement
     * @throws \Exception
     */
    protected function executeQuery($query)
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($this->bind);
        } finally {
            $this->cleanQuery();
        }

        return $stmt;
    }
}
