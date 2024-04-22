<?php

declare(strict_types=1);

namespace PhpMqdb\Tests\Integration;

trait ConfigTrait
{
    /**
     * @return array{
     *     dsn: string,
     *     host: string,
     *     name: string,
     *     username: string,
     *     password: string,
     *     options: array<int, string>,
     * }
     */
    protected function getPDOConfig(): array
    {
        $driver   = 'mysql';
        $host     = getenv('DB_HOST') ?: 'localhost';
        $name     = getenv('DB_NAME') ?: 'mqserver';
        $username = getenv('DB_USER') ?: 'user';
        $password = getenv('DB_PASSWORD') ?: 'pass';

        return [
            'dsn'      => "$driver:dbname=$name;host=$host",
            'host'     => $host,
            'name'     => $name,
            'username' => $username,
            'password' => $password,
            'options'  => [
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
            ],
        ];
    }

    /**
     * @return array{
     *     driver: 'pdo_mysql',
     *     host: string,
     *     user: string,
     *     password: string,
     *     dbname: string,
     * }
     */
    protected function getDBALConfig(): array
    {
        $driver   = 'pdo_mysql';
        $host     = getenv('DB_HOST') ?: 'localhost';
        $name     = getenv('DB_NAME') ?: 'mqserver';
        $username = getenv('DB_USER') ?: 'user';
        $password = getenv('DB_PASSWORD') ?: 'pass';

        return [
            'driver'   => $driver,
            'host'     => $host,
            'user'     => $username,
            'password' => $password,
            'dbname'   => $name,
        ];
    }
}
