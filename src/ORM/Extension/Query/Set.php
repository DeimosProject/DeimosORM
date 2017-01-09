<?php

namespace Deimos\ORM\Extension\Query;

/**
 * Class Set
 *
 * @package Deimos\ORM\Extension\Query
 */
trait Set
{

    /**
     * @var string
     */
    protected $set = '';

    /**
     * @var array
     */
    protected $storageSet = [];

    /**
     * @param array $storage
     *
     * @return static
     */
    public function setData(array $storage)
    {
        $this->storageSet = $storage;

        return $this;
    }

    /**
     * @param $column
     * @param $value
     *
     * @return static
     */
    public function set($column, $value)
    {
        $this->storageSet[$column] = $value;

        return $this;
    }

    /**
     * build set
     */
    protected function buildSet()
    {
        $storage = [];

        foreach ($this->storageSet as $column => $value)
        {
            $storage[] = $this->buildKey($column) . ' = ' . $this->buildValue($value);
        }

        $this->set = implode(', ', $storage);
    }

}