<?php

namespace Deimos\ORM;

use Deimos\ORM\Extension\Query\Set;

class InsertQuery extends Query
{

    use Set;

    /**
     * @var array
     */
    protected $operators = [
        'models' => 'INSERT INTO',
        'set'    => 'SET',
    ];

    /**
     * @return int
     */
    public function insert()
    {
        $this->statementExec();

        return $this->builder->connection()->lastInsertId();
    }

}