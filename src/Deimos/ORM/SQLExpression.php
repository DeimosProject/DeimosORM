<?php

namespace Deimos\ORM;

class SQLExpression
{

    /**
     * @var string
     */
    protected $sql;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * SQLExpression constructor.
     *
     * @param string $sql
     * @param array  $parameters
     */
    public function __construct($sql, $parameters = [])
    {
        $this->sql        = $sql;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getSQL()
    {
        return $this->sql;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

}