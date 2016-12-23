<?php

namespace Deimos\ORM;

class DeleteQuery extends Query
{

    /**
     * @var array
     */
    protected $operators = [
        'models' => 'DELETE FROM',
        'where'  => 'WHERE',
        'limit'  => 'LIMIT'
    ];

    /**
     * @return bool
     */
    public function deleteOne()
    {
        $this->limit(1);

        return !!$this->delete();
    }

    /**
     * @return int
     */
    public function delete()
    {
        $statement = $this->statementExec();

        return $statement->rowCount();
    }

}