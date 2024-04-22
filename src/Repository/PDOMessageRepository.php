<?php

/*
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMqdb\Repository;

use Eureka\Component\Database\ConnectionFactory;
use PhpMqdb\Query\QueryBuilder;

class PDOMessageRepository extends AbstractDatabaseMessageRepository
{
    private \PDO $connection;

    public function __construct(
        \PDO|null $connection = null,
        private readonly ConnectionFactory|null $connectionFactory = null,
        private readonly string $connectionName = '',
    ) {
        if ($this->connectionFactory instanceof ConnectionFactory) {
            $this->connection = $this->connectionFactory->getConnection($this->connectionName);
        } elseif ($connection instanceof \PDO) {
            $this->connection = $connection;
        } else {
            throw new \UnexpectedValueException('\PDO connection or ConnectionFactory must be set');
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return \PDOStatement
     * @throws \Exception
     */
    protected function executeQuery(QueryBuilder $queryBuilder)
    {
        try {
            $stmt = $this->connection->prepare($queryBuilder->getQuery());
            $stmt->execute($queryBuilder->getBind());
        } catch (\PDOException $exception) {
            if ($this->connectionFactory instanceof ConnectionFactory && $this->isConnectionLost($exception)) {
                $this->connection = $this->connectionFactory->getConnection('string');
                $stmt = $this->connection->prepare($queryBuilder->getQuery());
                $stmt->execute($queryBuilder->getBind());
            } else {
                throw $exception;
            }
        }

        return $stmt;
    }

    /**
     * @param \PDOException $exception
     * @return bool
     */
    protected function isConnectionLost(\PDOException $exception): bool
    {
        // Only keep SQLState HY000 with ErrorCode 2006 | 2013 (MySQL server has gone away)
        $sqlState = $exception->errorInfo[0] ?? 'UNKNW';
        $sqlCode  = $exception->errorInfo[1] ?? 1;
        return $sqlState === 'HY000' && in_array($sqlCode, [2006, 2013], true);
    }
}
