<?php declare(strict_types=1);

/*
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Repository;

use PhpMqdb\Query\QueryBuilder;

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
     */
    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return \PDOStatement
     * @throws \Exception
     */
    protected function executeQuery(QueryBuilder $queryBuilder)
    {
        $stmt = $this->connection->prepare($queryBuilder->getQuery());
        $stmt->execute($queryBuilder->getBind());

        return $stmt;
    }
}
