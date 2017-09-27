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

    /** @var */
    private $dsn;

    /** @var */
    private $username;

    /** @var */
    private $password;

    /**
     * PDOMessageRepository constructor.
     * @param $dsn
     * @param $username
     * @param $password
     * @param string $classFactory
     */
    public function __construct($dsn, $username, $password, $classFactory = Message\MessageFactory::class)
    {
        $this->setClassMessageFactory($classFactory);
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->connect();
    }

    /**
     *
     */
    protected function connect()
    {
        $this->connection = new \PDO($this->dsn, $this->username, $this->password);
        $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
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

            } catch (\PDOException $exception) {

                // Only keep SQLState HY000 with ErrorCode 2006 (MySQL server has gone away)
                if ($exception->errorInfo[1] !== 2006 || $exception->errorInfo[0] !== 'HY000') {
                    throw $exception;
                }

                //  => force reconnect + replay query
                $this->connect();
                $stmt = $this->connection->prepare($query);
                $stmt->execute($this->bind);

            }

        } finally {
            $this->cleanQuery();
        }

        return $stmt;
    }
}
