<?php

namespace Deimos\ORM\Extension\Query;

/**
 * Class Limit
 *
 * @package Deimos\ORM\Extension\Query
 */
trait Limit
{

    /**
     * @var string
     */
    protected $limit = '';

    /**
     * @var int
     */
    protected $storageLimit;

    /**
     * @param $limit
     *
     * @return static
     */
    public function take($limit)
    {
        return $this->limit($limit);
    }

    /**
     * @param $limit
     *
     * @return static
     */
    public function limit($limit)
    {
        $this->storageLimit = $limit;

        return $this;
    }

    /**
     * build limit
     *
     * @throws \InvalidArgumentException
     */
    protected function buildLimit()
    {
        $isOffset = property_exists($this, 'storageOffset');

        if ($this->storageLimit)
        {
            $this->limit = '';

            if ($isOffset && $this->storageOffset)
            {
                $this->limit = $this->storageOffset . ', ';
            }

            $this->limit .= $this->storageLimit;

            return;
        }

        if ($isOffset && $this->storageOffset)
        {
            throw new \InvalidArgumentException('Offset without limit!');
        }
    }

}