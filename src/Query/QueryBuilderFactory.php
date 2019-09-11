<?php declare(strict_types=1);

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMqdb\Query;

use PhpMqdb\Config\TableConfig;

/**
 * Class QueryBuilderFactory
 *
 * @author Romain Cottard
 */
class QueryBuilderFactory
{
    /** @var TableConfig $tableConfig */
    private $tableConfig;

    /**
     * QueryBuilderFactory constructor.
     *
     * @param TableConfig $tableConfig
     */
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
