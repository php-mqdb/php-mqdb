<?php

/*
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMqdb\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Exception\DeadlockException;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Result;
use PhpMqdb\Query\QueryBuilder;

/**
 * Interface for Message Repository
 *
 * @author Romain Cottard
 */
class DBALMessageRepository extends AbstractDatabaseMessageRepository
{
    private Connection $connection;

    /**
     * AbstractDatabaseMessageRepository constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return Result
     * @throws \Exception
     * @throws Exception
     */
    protected function executeQuery(QueryBuilder $queryBuilder)
    {
        $query = $queryBuilder->getQuery();
        $bind  = $queryBuilder->getBind();

        $stmt  = $this->connection->prepare($query);

        try {
            $result = @$stmt->executeQuery($bind);
        } catch (DriverException | DeadlockException $exception) {
            // Keep SQLState HY000 with ErrorCode 2006 (MySQL server has gone away)
            // And SQLState 40001 (Serialization failure: Deadlock found when trying to get lock)
            if (!in_array($exception->getCode(), [2006, 0]) || !in_array($exception->getSQLState(), ['HY000', '40001'])) {
                throw $exception;
            }

            // Sleep between 25 & 100ms before retrying to prevent connections to retry at same time
            usleep(mt_rand(25000, 100000));

            $this->connection->close();
            $stmt = $this->connection->prepare($query);
            $result = $stmt->executeQuery($bind);
        }

        return $result;
    }
}
