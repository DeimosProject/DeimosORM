<?php

namespace Deimos\ORM;

use Deimos\ORM\Extension\Query\BuildExt;
use Deimos\ORM\Extension\Query\Model;

abstract class Query
{

    use BuildExt;

    use Model;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * for Insert/Update/Select/Delete Query
     *
     * @var array
     */
    protected $operators = [];

    /**
     * for Insert/Update/Select/Delete Query
     *
     * @var array
     */
    protected $defaults = [];

    /**
     * Query constructor.
     *
     * @param $builder Builder
     */
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $sql              = [];
        $this->parameters = [];

        foreach ($this->operators as $name => $operator)
        {
            $ucFirst = ucfirst($name);

            $methodName = 'build' . $ucFirst;

            $storageName = 'storage' . $ucFirst;

            if (!empty($this->{$storageName}))
            {
                $this->{$methodName}();

                $sql[] = $operator . ' ' . $this->{$name};
            }
            else if (!empty($this->defaults[$name]))
            {
                $sql[] = $operator . ' ' . $this->defaults[$name];
            }
        }

        return implode(' ', $sql);
    }

    /**
     * @param $value
     *
     * @return string
     */
    protected function gravis($value)
    {
        return "`$value`";
    }

}