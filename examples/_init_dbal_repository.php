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
