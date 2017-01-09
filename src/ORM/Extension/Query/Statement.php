<?php

namespace Deimos\ORM\Extension\Query;

use Deimos\ORM\Builder;
use Deimos\ORM\Connection;

/**
 * Class Statement
 *
 * @package Deimos\ORM\Extension\Query
 *
 * @param array   $parameters
 * @param Builder $builder
 */
trait Statement
{

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @var array
     */
    protected $storageParameters = [];

    /**
     * @param null|string $sql
     * @param array       $parameters
     *
     * @return \PDOStatement
     */
    protected function statementExec($sql = null, array $parameters = [])
    {
        $statement = $this->statement($sql);
        $statement->execute($parameters ?: $this->parameters());

        return $statement;
    }

    /**
     * @param null|string $sql
     *
     * @return \PDOStatement
     */
    protected function statement($sql = null)
    {
        static $statements = [];

        /**
         * @var $connection Connection
         */
        $connection = $this->builder->connection();

        /**
         * @var $sqlQuery string
         */
        $sqlQuery = $sql ?: (string)$this;

        if (empty($statements[$sqlQuery]))
        {
            $statements[$sqlQuery] = $connection->prepare($sqlQuery);
        }

        return $statements[$sqlQuery];
    }

    /**
     * @return array
     */
    public function parameters()
    {
        return array_merge($this->storageParameters, $this->parameters);
    }

}