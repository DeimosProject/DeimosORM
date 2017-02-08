<?php

namespace Deimos\ORM;

use Deimos\Database\Database;
use Deimos\ORM\Exceptions\ModelNotLoad;
use Deimos\ORM\Exceptions\ModelNotModify;
use Doctrine\Common\Inflector\Inflector;

class Entity implements \JsonSerializable
{

    /**
     * @var ORM
     */
    protected $orm;


    /**
     * @var Database
     */
    protected $database;

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
     * @var string
     */
    protected $modelName;

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
     * @param ORM    $orm
     * @param bool   $isNew
     * @param string $table
     */
    public function __construct($orm, $isNew = true, $table = null)
    {
        $this->table    = $table;
        $this->orm      = $orm;
        $this->database = $orm->database();
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
     * @param string $value
     */
    public function setModelName($value)
    {
        $this->modelName = $value;
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return array|static
     *
     * @throws \InvalidArgumentException
     */
    public function __call($name, array $arguments)
    {
        if (!$this->modelName)
        {
            throw new \InvalidArgumentException('Model Name not found');
        }

        $config = $this->orm->config($this->modelName);

        if (!$config)
        {
            throw new \InvalidArgumentException('Config model not found');
        }

        return $this->relation($config, $arguments);
    }

    /**
     * @param array $config
     * @param array $arguments
     *
     * @return array|static
     */
    protected function relation(array &$config, array &$arguments)
    {
        return $this->{$config['type']}($config, $arguments);
    }

    protected function oneToMany(array &$config, array &$arguments)
    {
        throw new \InvalidArgumentException(__METHOD__);
    }

    protected function manyToMany(array &$config, array &$arguments)
    {
        throw new \InvalidArgumentException(__METHOD__);
    }

    /**
     * @param array $storage
     *
     * @return bool
     *
     * @throws ModelNotLoad
     * @throws ModelNotModify
     */
    public function save(array $storage = [])
    {
        foreach ($storage as $key => $value)
        {
            $this->set($key, $value);
        }

        if (empty($this->modify))
        {
            throw new ModelNotModify($this->table);
        }

        if ($this->isNew)
        {
            $insertId = $this->database()->insert()
                ->from($this->tableName())
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
            throw new ModelNotLoad($this->table);
        }

        $update = (bool)$this->database()->update()
            ->from($this->tableName())
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
     * @return bool
     *
     * @throws ModelNotLoad
     */
    public function delete()
    {
        if (!$this->isLoad)
        {
            throw new ModelNotLoad($this->table);
        }

        $this->modify = $this->asArray();
        $this->origin = [];

        $delete = (bool)$this->database()->delete()
            ->from($this->tableName())
            ->where($this->primaryKey, $this->id())
            ->delete();

        unset($this->modify[$this->primaryKey]);
        $this->isNew  = true;
        $this->isLoad = false;

        return $delete;
    }

    /**
     * @return array
     */
    public function asArray()
    {
        return array_merge($this->origin, $this->modify);
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return $this->asArray();
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