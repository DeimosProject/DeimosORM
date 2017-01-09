<?php

namespace Deimos\ORM\Extension\Query;

/**
 * Class Where
 *
 * @package Deimos\ORM\Extension\Query
 */
trait Having
{

    /**
     * @var string
     */
    protected $having = '';

    /**
     * @var array
     */
    protected $storageHaving = [];

    /**
     * ('id', 'name', ...)
     *
     * @param array ...$having
     *
     * @return static
     */
    public function having(...$having)
    {
        $this->storageHaving[] = ['AND' => $having];

        return $this;
    }

    /**
     * ('id', 'name', ...)
     *
     * @param array ...$having
     *
     * @return static
     */
    public function havingOr(...$having)
    {
        $this->storageHaving[] = ['OR' => $having];

        return $this;
    }

    /**
     * ('id', 'name', ...)
     *
     * @param array ...$having
     *
     * @return static
     */
    public function havingXor(...$having)
    {
        $this->storageHaving[] = ['XOR' => $having];

        return $this;
    }

    /**
     * build having
     */
    protected function buildHaving()
    {
        /**
         * @var array $having
         */
        $having = $this->buildWhereOperator($this->storageHaving);

        $this->having        = '';
        $this->allowOperator = false;
        $this->buildIf2String([$having], $this->having);
    }

}