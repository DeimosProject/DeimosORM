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
     * @var int
     */
    protected $level = 0;

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
     * start transaction
     */
    public function start()
    {
        if (!$this->level++)
        {
            $this->builder->connection()->beginTransaction();
        }
    }

    /**
     * @return bool
     */
    public function isStarted()
    {
        return $this->builder->connection()->inTransaction();
    }

    /**
     * @return null|int
     *
     * @throws \InvalidArgumentException
     */
    public function end()
    {
        if (--$this->level < 0)
        {
            throw new \InvalidArgumentException('Has gone beyond transaction!');
        }

        $this->state = static::STATE_COMMIT;
        if (!$this->builder->connection()->commit())
        {
            $this->state = static::STATE_ROLLBACK;
            return $this->builder->connection()->rollBack();
        }

        return $this->state;
    }

    /**
     * @return int
     */
    public function level()
    {
        return $this->level;
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
        $this->start();

        $result = null;
        try
        {
            $result = $callable($this->builder);
            $this->end();
        }
        catch (\Exception $exception)
        {
            --$this->level;
            $this->state = static::STATE_ROLLBACK;
            $this->builder->connection()->rollBack();
        }

        return $result;
    }

    /**
     * @return null|int
     */
    public function state()
    {
        return $this->state;
    }

}