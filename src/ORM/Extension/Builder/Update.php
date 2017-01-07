<?php

namespace Deimos\ORM\Extension\Builder;

use Deimos\ORM\UpdateQuery;

trait Update
{

    /**
     * @param $class
     *
     * @return UpdateQuery
     */
    public function updateEntity($class)
    {
        return $this->update()->model($class);
    }

    /**
     * @return UpdateQuery
     */
    public function update()
    {
        $update = $this->options['update'];

        return new $update($this);
    }

}