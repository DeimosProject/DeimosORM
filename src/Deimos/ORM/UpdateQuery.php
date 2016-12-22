<?php

namespace Deimos\ORM;

class UpdateQuery extends Query
{

    /**
     * @var array
     */
    protected $operators = [
        'models'  => 'UPDATE',
        'set'     => 'SET',
        'where'   => 'WHERE',
        'orderBy' => 'ORDER BY',
        'limit'   => 'LIMIT'
    ];

    /**
     * @return bool
     */
    public function updateOne()
    {
        $this->limit(1);

        return !!$this->update();
    }

    /**
     * @return int
     */
    public function update()
    {
        $statement = $this->statement();
        $statement->execute($this->parameters());

        return $statement->rowCount();
    }

}