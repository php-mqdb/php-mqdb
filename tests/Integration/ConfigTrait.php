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
        $host     = self::getEnv('DB_HOST', 'localhost');
        $name     = self::getEnv('DB_NAME', 'mqserver');
        $username = self::getEnv('DB_USER', 'user');
        $password = self::getEnv('DB_PASSWORD', 'pass');

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
        $host     = self::getEnv('DB_HOST', 'localhost');
        $name     = self::getEnv('DB_NAME', 'mqserver');
        $username = self::getEnv('DB_USER', 'user');
        $password = self::getEnv('DB_PASSWORD', 'pass');

        return [
            'driver'   => $driver,
            'host'     => $host,
            'user'     => $username,
            'password' => $password,
            'dbname'   => $name,
        ];
    }

    private static function getEnv(string $name, string $default): string
    {
        $value = getenv($name);

        if (!is_string($value)) {
            return $default;
        }

        return $value;
    }
}
