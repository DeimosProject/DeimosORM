<?php

namespace Deimos\ORM\Extension\Query;

trait Option
{

    /**
     * @var string
     */
    protected $option = '';

    /**
     * @var array
     */
    protected $storageOption;

    /**
     * @param string $key
     * @param string $value
     *
     * @return static
     * @deprecated use sphinx se
     */
    public function sphinxOption($key = 'ranker', $value = 'matchany')
    {
        $this->storageOption = "$key = $value";

        return $this;
    }

    /**
     * @deprecated use sphinx se
     */
    protected function buildOption()
    {
        $this->option = $this->storageOption;
    }

}