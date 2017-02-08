<?php

namespace Deimos\ORM;

use Deimos\Database\Database;
use Doctrine\Common\Inflector\Inflector;

class Entity
{

    /**
     * @var Database
     */
    private $database;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var bool
     */
    protected $isLoad;

    /**
     * @var bool
     */
    protected $isNew;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $origin = [];

    /**
     * @var array
     */
    protected $modify = [];

    /**
     * User constructor.
     *
     * @param Database $database
     * @param bool     $isNew
     * @param string   $table
     */
    public function __construct($database, $isNew = true, $table = null)
    {
        $this->table    = $table;
        $this->database = $database;
        $this->isNew    = $isNew;

        if ($this->isNew)
        {
            $this->isLoad = false;
        }
    }

    /**
     * @return mixed
     */
    public function id()
    {
        return $this->get($this->primaryKey);
    }

    /**
     * @return string
     */
    public function tableName()
    {
        if (!$this->table && self::class !== static::class)
        {
            $ref   = new \ReflectionClass(static::class);
            $table = $ref->getName();
            $table = lcfirst($table);

            return Inflector::pluralize($table);
        }

        return $this->table;
    }

    /**
     * @return Database
     */
    protected function database()
    {
        return $this->database;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function get($name)
    {
        if (isset($this->modify[$name]))
        {
            return $this->modify[$name];
        }

        return $this->origin[$name];
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function set($name, $value)
    {
        $this->modify[$name] = $value;
    }

    /**
     * load data
     */
    protected function toLoad()
    {
        $this->origin = array_merge($this->origin, $this->modify);

        $this->modify = [];
        $this->isNew  = false;
        $this->isLoad = true;
    }

    /**
     * @param array $storage
     *
     * @return bool
     */
    public function save(array $storage = [])
    {
        foreach ($storage as $key => $value)
        {
            $this->set($key, $value);
        }

        if ($this->isNew)
        {
            $insertId = $this->database()->insert()
                ->from($this->table)
                ->values($this->modify)
                ->insert();

            if ($insertId)
            {
                $this->set($this->primaryKey, $insertId);
                $this();
            }

            return (bool)$insertId;
        }

        if (!$this->isLoad)
        {
            throw new \InvalidArgumentException(__CLASS__);
        }

        $update = (bool)$this->database()->update()
            ->from($this->table)
            ->values($this->modify)
            ->where($this->primaryKey, $this->id())
            ->updateOne();

        if ($update)
        {
            $this();
        }

        return $update;
    }

    /**
     * @return array
     */
    public function asArray()
    {
        return array_merge($this->origin, $this->modify);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->origin[$name]);
    }

    /**
     * load data
     */
    public function __invoke()
    {
        $this->toLoad();
    }

}