<?php

namespace Deimos\ORM\Extension\Builder;

use Deimos\ORM\Builder;
use Deimos\ORM\SQLExpression;

trait RawQuery
{

    use Query;
    use Transaction;

    /**
     * @param string $sql
     * @param array  $parameters
     *
     * @return \PDOStatement
     *
     * @throws \InvalidArgumentException
     */
    public function rawQuery($sql, array $parameters = [])
    {
        return $this->transaction()->call(function ($builder) use ($sql, $parameters)
        {
            /**
             * @var $builder Builder
             */
            return $builder->query()->raw($sql, $parameters);
        });
    }

    /**
     * @param string $sql
     * @param array  $parameters
     *
     * @return SQLExpression
     */
    public function sqlExpression($sql, array $parameters = [])
    {
        return new SQLExpression($sql, $parameters);
    }

}