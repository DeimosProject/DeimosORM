<?php

namespace Deimos\ORM\Extension\Builder;

use Deimos\ORM\Entity;
use Deimos\ORM\InsertQuery;
use Deimos\ORM\Reflection;

trait Create
{

    /**
     * @return InsertQuery
     */
    public function create()
    {
        $create = $this->options['insert'];

        return new $create($this);
    }

    /**
     * @param $class
     *
     * @return Entity
     */
    public function createEntity($class)
    {
        $tableName = Reflection::getTableName($class); // todo

        return new $class($this, Entity::STATE_CREATED, $tableName);
    }

}