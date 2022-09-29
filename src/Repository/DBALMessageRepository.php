<?php declare(strict_types=1);

/*
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Exception\DeadlockException;
use Doctrine\DBAL\Exception\DriverException;
use PhpMqdb\Query\QueryBuilder;

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
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return Statement
     * @throws \Exception
     */
    protected function executeQuery(QueryBuilder $queryBuilder)
    {
        $query = $queryBuilder->getQuery();
        $bind  = $queryBuilder->getBind();

        $stmt  = $this->connection->prepare($query);

        try {
            @$stmt->execute($bind);
        } catch (DriverException | DeadlockException $exception) {

            // Keep SQLState HY000 with ErrorCode 2006 (MySQL server has gone away)
            // And SQLState 40001 (Serialization failure: Deadlock found when trying to get lock)
            if ($exception->getErrorCode() !== 2006 || !in_array($exception->getSQLState(), ['HY000', '40001'])) {
                throw $exception;
            }

            // Sleep 1/10 second
            usleep(100000);

            $this->connection->close();
            $stmt = $this->connection->prepare($query);
            $stmt->execute($bind);
        }

        return $stmt;
    }
}
