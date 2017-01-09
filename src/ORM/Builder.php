<?php

namespace Deimos\ORM;

use Deimos\ORM\Extension\Builder\Create;
use Deimos\ORM\Extension\Builder\Delete;
use Deimos\ORM\Extension\Builder\Relation;
use Deimos\ORM\Extension\Builder\Update;

class Builder
{

    use Relation;
    use Create;
    use Update;
    use Delete;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * Builder constructor.
     *
     * @param string $name
     */
    public function __construct($name = null)
    {
        if ($name)
        {
            $this->setConnection($name);
        }
    }

    /**
     * @param string $name
     */
    public function setConnection($name = 'default')
    {
        $this->connection = Connection::get($name);
    }

    /**
     * @return Config
     */
    public function config()
    {
        if (!$this->config)
        {
            $this->config = new Config($this->reflection());
        }

        return $this->config;
    }

    /**
     * @return Reflection
     */
    public function reflection()
    {
        if (!$this->reflection)
        {
            $this->reflection = new Reflection();
        }

        return $this->reflection;
    }

    /**
     * @return Connection
     */
    public function connection()
    {
        if (!$this->connection)
        {
            $this->setConnection();
        }

        return $this->connection;
    }

}