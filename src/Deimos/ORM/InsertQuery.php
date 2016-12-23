<?php

namespace Deimos\ORM;

class InsertQuery extends Query
{

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