<?php

namespace Deimos\ORM\Extension\Builder;

use Deimos\ORM\Entity;
use Deimos\ORM\InsertQuery;
use Deimos\ORM\Reflection;

trait Create
{

    /**
     * @var Reflection $reflection
     */

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
        /**
         * @var Reflection $ref
         */
        $ref       = $this->reflection;
        $tableName = $ref->getTableName($class);

        return new $class($this, Entity::STATE_CREATED, $tableName);
    }

}