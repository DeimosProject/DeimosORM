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
        $statement = $this->statement();
        $statement->execute($this->parameters);

        return $this->builder->connection()->lastInsertId();
    }

}