<?php

namespace Deimos\ORM;

class Transaction
{

    const STATE_ROLLBACK = 0;
    const STATE_COMMIT   = 1;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var null|int
     */
    protected $state;

    /**
     * Transaction constructor.
     *
     * @param Builder $builder
     */
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @param callable $callable
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function call(callable $callable)
    {
        $this->builder->connection()->beginTransaction();

        try
        {
            $result = $callable($this->builder);
            $this->builder->connection()->commit();
            $this->state = static::STATE_COMMIT;
        }
        catch (\Exception $exception)
        {
            $result      = null;
            $this->state = static::STATE_ROLLBACK;
            $this->builder->connection()->rollBack();
        }
        finally
        {
            return $result;
        }
    }

    /**
     * @return null|int
     */
    public function state()
    {
        return $this->state;
    }

}