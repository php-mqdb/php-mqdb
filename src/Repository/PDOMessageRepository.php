<?php

/*
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMqdb\Repository;

use PhpMqdb\Query\QueryBuilder;

class PDOMessageRepository extends AbstractDatabaseMessageRepository
{
    /**
     * PDOMessageRepository constructor.
     *
     * @param \PDO $connection
     */
    public function __construct(
        private readonly \PDO $connection
    ) {}

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
