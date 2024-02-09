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
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\DeadlockException;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use PhpMqdb\Query\QueryBuilder;

class DBALMessageRepository extends AbstractDatabaseMessageRepository
{
    public function __construct(private readonly Connection $connection) {}

    /**
     * @param QueryBuilder $queryBuilder
     * @return Result
     * @throws Exception
* @throws \Exception
     */
    protected function executeQuery(QueryBuilder $queryBuilder)
    {
        $query = $queryBuilder->getQuery();
        $bind  = $queryBuilder->getBind();

        $stmt  = $this->connection->prepare($query);
        $stmt  = $this->bindValues($stmt, $bind);

        try {
            $result = $stmt->executeQuery();
        } catch (DriverException | DeadlockException $exception) {
            // Keep SQLState HY000 with ErrorCode 2006 (MySQL server has gone away)
            // And SQLState 40001 (Serialization failure: Deadlock found when trying to get lock)
            if (
                !\in_array($exception->getCode(), [2006, 0], true) ||
                !\in_array($exception->getSQLState(), ['HY000', '40001'], true)
            ) {
                throw $exception;
            }

            // Sleep between 25 & 100ms before retrying to prevent connections to retry at same time
            \usleep(\mt_rand(25000, 100000));

            $this->connection->close();
            $stmt = $this->connection->prepare($query);
            $stmt = $this->bindValues($stmt, $bind);
            $result = $stmt->executeQuery();
        }

        return $result;
    }

    /**
     * @param array<string, int|string|null> $bind
     * @throws Exception
     */
    private function bindValues(Statement $statement, array $bind): Statement
    {
        foreach ($bind as $key => $value) {
            //~ Values are only int or string
            $type = match(true) {
                is_int($value)  => ParameterType::INTEGER,
                $value === null => ParameterType::NULL,
                default         => ParameterType::STRING,
            };

            $statement->bindValue($key, $value, $type);
        }

        return $statement;
    }
}
