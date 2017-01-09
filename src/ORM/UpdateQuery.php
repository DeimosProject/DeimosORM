<?php

namespace Deimos\ORM;

use Deimos\ORM\Extension\Query\Limit;
use Deimos\ORM\Extension\Query\OrderBy;
use Deimos\ORM\Extension\Query\Set;
use Deimos\ORM\Extension\Query\Where;

class UpdateQuery extends Query
{

    use Set;
    use Where;
    use OrderBy;
    use Limit;

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
        $object = clone $this;
        $object->limit(1);

        return !!$object->update();
    }

    /**
     * @return int
     */
    public function update()
    {
        return $this->statementExec()->rowCount();
    }

}