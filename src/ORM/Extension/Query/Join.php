<?php

namespace Deimos\ORM\Extension\Query;

use Deimos\ORM\SQLExpression;

/**
 * Class Join
 *
 * @package Deimos\ORM\Extension\Query
 */
trait Join
{

    /**
     * @var string
     */
    protected $join = '';

    /**
     * @var array
     */
    protected $storageJoin = [];

    /**
     * @param               $model
     * @param SQLExpression $expression
     *
     * @return static
     */
    public function join($model, SQLExpression $expression)
    {
        return $this->joinWithType($model, $expression);
    }

    /**
     * @param               $model
     * @param SQLExpression $expression
     * @param string        $type
     *
     * @return static
     */
    public function joinWithType($model, SQLExpression $expression, $type = '')
    {
        $this->setJoin($model, $expression, $type);

        return $this;
    }

    /**
     * @param string        $model
     * @param SQLExpression $expression
     * @param string        $type
     */
    protected function setJoin($model, $expression, $type)
    {
        $this->storageParameters = array_merge($this->storageParameters, $expression->getParameters());
        $this->storageJoin[]     = implode(' ', [
            $type . ' JOIN',
            $this->buildTable($model),
            'ON',
            $expression->getSQL()
        ]);
    }

    /**
     * @param               $model
     * @param SQLExpression $expression
     *
     * @return static
     */
    public function joinRight($model, SQLExpression $expression)
    {
        return $this->joinWithType($model, $expression, 'RIGHT');
    }

    /**
     * @param               $model
     * @param SQLExpression $expression
     *
     * @return static
     */
    public function joinLeft($model, SQLExpression $expression)
    {
        return $this->joinWithType($model, $expression, 'LEFT');
    }

    /**
     * build join
     */
    protected function buildJoin()
    {
        $this->join = implode(' ', $this->storageJoin);
    }

}