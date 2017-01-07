<?php

namespace Deimos\ORM\Extension\Builder;

use Deimos\ORM\DeleteQuery;

trait Delete
{

    /**
     * @param $class
     *
     * @return DeleteQuery
     */
    public function deleteEntity($class)
    {
        return $this->delete()->model($class);
    }

    /**
     * @return DeleteQuery
     */
    public function delete()
    {
        $delete = $this->options['delete'];

        return new $delete($this);
    }

}