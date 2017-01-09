<?php

namespace Deimos\ORM\Extension\Query;

trait Offset
{

    /**
     * @var int
     */
    protected $storageOffset;

    /**
     * @param $offset
     *
     * @return static
     */
    public function skip($offset)
    {
        return $this->offset($offset);
    }

    /**
     * @param $offset
     *
     * @return static
     */
    public function offset($offset)
    {
        $this->storageOffset = $offset;

        return $this;
    }

}