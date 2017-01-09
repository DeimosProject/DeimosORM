<?php

namespace Deimos\ORM\Extension\Query;

/**
 * Class GroupBy
 *
 * @package Deimos\ORM\Extension\Query
 */
trait GroupBy
{

    /**
     * @var string
     */
    protected $groupBy = '';

    /**
     * @var array
     */
    protected $storageGroupBy = [];

    /**
     * @param $field
     *
     * @return static
     */
    public function groupBy($field)
    {
        $this->storageGroupBy[] = $this->buildKey($field);

        return $this;
    }

    /**
     * build group by
     */
    protected function buildGroupBy()
    {
        $this->groupBy = implode(' ', $this->storageGroupBy);
    }

}