<?php

namespace Deimos\ORM\Extension\Builder;

use Deimos\ORM\Builder;

trait Transaction
{

    /**
     * @var \Deimos\ORM\Transaction
     */
    protected $transaction;

    /**
     * @return \Deimos\ORM\Transaction
     *
     * @throws \InvalidArgumentException
     */
    public function transaction()
    {
        if (!$this->transaction)
        {
            if ($this instanceof Builder)
            {
                $this->transaction = new \Deimos\ORM\Transaction($this);
            }
            else
            {
                throw new \InvalidArgumentException('Builder error!');
            }
        }

        return $this->transaction;
    }

}