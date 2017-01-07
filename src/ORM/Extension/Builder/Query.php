<?php

namespace Deimos\ORM\Extension\Builder;

use Deimos\ORM\DeleteQuery;
use Deimos\ORM\InsertQuery;
use Deimos\ORM\SelectQuery;
use Deimos\ORM\UpdateQuery;

trait Query
{

    /**
     * @var array
     */
    protected $options = [
        'query'  => SelectQuery::class,
        'insert' => InsertQuery::class,
        'update' => UpdateQuery::class,
        'delete' => DeleteQuery::class,
    ];

    /**
     * @return SelectQuery
     */
    public function query()
    {
        $query = $this->options['query'];

        return new $query($this);
    }

    /**
     * @param $class
     *
     * @return SelectQuery
     */
    public function queryEntity($class)
    {
        return $this->query()->model($class);
    }

}