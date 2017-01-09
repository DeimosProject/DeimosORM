<?php

namespace Deimos\ORM\Extension\Query;

/**
 * Class Where
 *
 * @package Deimos\ORM\Extension\Query
 */
trait Where
{

    /**
     * @var string
     */
    protected $where = '';

    /**
     * @var array
     */
    protected $storageWhere = [];

    /**
     * @param $sql
     *
     * @return static
     * @deprecated use sphinx se
     */
    public function sphinxMatch($sql)
    {
        $expression = $this->builder->sqlExpression('MATCH(?)', [$sql]);

        return $this->where($expression);
    }

    /**
     * ('id', 'name', ...)
     *
     * @param array ...$where
     *
     * @return static
     */
    public function where(...$where)
    {
        $this->storageWhere[] = ['AND' => $where];

        return $this;
    }

    /**
     * ('id', 'name', ...)
     *
     * @param array ...$where
     *
     * @return static
     */
    public function whereOr(...$where)
    {
        $this->storageWhere[] = ['OR' => $where];

        return $this;
    }

    /**
     * ('id', 'name', ...)
     *
     * @param array ...$where
     *
     * @return static
     */
    public function whereXor(...$where)
    {
        $this->storageWhere[] = ['XOR' => $where];

        return $this;
    }

    /**
     * build where
     */
    protected function buildWhere()
    {
        /**
         * @var array $where
         */
        $where = $this->buildWhereOperator($this->storageWhere);

        $this->where         = '';
        $this->allowOperator = false;
        $this->buildIf2String([$where], $this->where);
    }

}