<?php

namespace Deimos\ORM;

use Deimos\ORM\Extension\Query\Limit;
use Deimos\ORM\Extension\Query\Where;

class DeleteQuery extends Query
{

    use Where;
    use Limit;

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
        $object = clone $this;
        $object->limit(1);

        return !!$object->delete();
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