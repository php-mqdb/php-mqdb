<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMqdb\Query;

use PhpMqdb\Config\TableConfig;

class QueryBuilderFactory
{
    private TableConfig $tableConfig;

    public function __construct(TableConfig $tableConfig)
    {
        $this->tableConfig = $tableConfig;
    }

    /**
     * @return QueryBuilder
     */
    public function getBuilder(): QueryBuilder
    {
        return new QueryBuilder($this->tableConfig);
    }
}
