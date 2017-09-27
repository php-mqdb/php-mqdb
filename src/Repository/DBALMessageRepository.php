<?php

/**
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DriverException;
use PhpMqdb\Message;

/**
 * Interface for Message Repository
 *
 * @author Romain Cottard
 */
class DBALMessageRepository extends AbstractDatabaseMessageRepository
{
    /** @var Connection $connection */
    private $connection = null;

    /**
     * AbstractDatabaseMessageRepository constructor.
     *
     * @param Connection $connection
     * @param string $classFactory
     */
    public function __construct(Connection $connection, $classFactory = Message\MessageFactory::class)
    {
        $this->connection = $connection;
        $this->setClassMessageFactory($classFactory);
    }

    /**
     * @param string $query
     * @return \Doctrine\DBAL\Driver\Statement|\PDOStatement
     * @throws \Exception
     */
    protected function executeQuery($query)
    {
        try {

            $stmt = $this->connection->prepare($query);

            try {
                @$stmt->execute($this->bind);

            } catch (DriverException $exception) {

                // Only keep SQLState HY000 with ErrorCode 2006 (MySQL server has gone away)
                if ($exception->getErrorCode() !== 2006 || $exception->getSQLState() !== 'HY000') {
                    throw $exception;
                }

                $this->connection->close();
                $stmt = $this->connection->prepare($query);
                $stmt->execute($this->bind);
            }

        } finally {
            $this->cleanQuery();
        }

        return $stmt;
    }
}
