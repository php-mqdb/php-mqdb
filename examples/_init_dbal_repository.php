<?php

/*
 * Copyright Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMqdb\Examples;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PhpMqdb\Repository\DBALMessageRepository;

$dbConf = require_once __DIR__ . '/_config.php';

/*
$dbConf->driver = 'mysql';
$dbConf->name   = 'mqserver';
$dbConf->host   = '127.0.0.1';
$dbConf->user   = 'user';
$dbConf->pass   = 'pass';

$dbConf->dsn  = "$dbConf->driver:dbname=$dbConf->name;host=$dbConf->host";
$dbConf->opts = [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''];
 */
$connectionParams = [
    'driver'   => 'pdo_mysql',
    'host'     => $dbConf->host,
    'user'     => $dbConf->user,
    'password' => $dbConf->pass,
    'dbname'   => $dbConf->name,
];

//~ Connection
$connection = DriverManager::getConnection($connectionParams);

//~ Repository
$repository = new DBALMessageRepository($connection);

return $repository;
