<?php

namespace Deimos\ORM\Extension\Query;

/**
 * Class OrderBy
 *
 * @package Deimos\ORM\Extension\Query
 */
trait OrderBy
{

    /**
     * @var string
     */
    protected $orderBy = '';

    /**
     * @var array
     */
    protected $storageOrderBy = [];

    /**
     * @param        $field
     * @param string $direction
     *
     * @return static
     */
    public function orderBy($field, $direction = 'ASC')
    {
        $this->storageOrderBy[] = $this->buildKey($field) . ' ' . $direction;

        return $this;
    }

    /**
     * build order by
     */
    protected function buildOrderBy()
    {
        $this->orderBy = implode(' ', $this->storageOrderBy);
    }

}