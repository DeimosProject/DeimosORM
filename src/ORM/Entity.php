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
     * @var array
     */
    protected $relations = [];

    /**
     * User constructor.
     *
     * @param ORM    $orm
     * @param bool   $isNew
     * @param string $table
     */
    public function __construct($orm, $isNew = true, $table = null)
    {
        if ($table)
        {
            $this->table    = $table;
        }

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
    public function primaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @return string
     */
    public function tableName()
    {
        if (!$this->table && self::class !== static::class)
        {
            $ref   = new \ReflectionClass(static::class);
            $table = $ref->getShortName();
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
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($name, $default = null)
    {
        $configure = $this->orm->config($this->modelName);

        if (isset($configure[$name]))
        {
            if ($configure[$name]['type'] === 'oneToMany' && $configure[$name]['model'] === $name)
            {
                if (!isset($this->relations[$name]))
                {
                    $this->relations[$name] = $this->$name()->findOne();
                }

                return $this->relations[$name];
            }

            return $this->$name();
        }

        if (isset($this->modify[$name]))
        {
            return $this->modify[$name];
        }

        return $this->origin[$name] ?? $default;
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
     *
     * @return static
     */
    public function set($name, $value)
    {
        $this->modify[$name] = $value;

        return $this;
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
     * @return Queries\Query
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
            throw new \InvalidArgumentException('Config model `' . $this->modelName . '` not found');
        }

        return $this->relation($config[$name], $arguments);
    }

    /**
     * @param array $config
     * @param array $arguments
     *
     * @return Queries\Query
     */
    protected function relation(array &$config, array &$arguments)
    {
        return $this->{$config['type']}($config, $arguments);
    }

    /**
     * @param array $config
     * @param array $arguments
     *
     * @return Queries\Query
     */
    protected function oneToMany(array &$config, array &$arguments)
    {
        $key = $this->primaryKey;

        if ($config['itemId'])
        {
            $key = $config['itemId'];
        }
        else if ($config['model'] !== $config['item'])
        {
            $key = $config['from'] . ucfirst($this->primaryKey);
        }

        return $this->orm->repository($config['model'])
            ->where($key, $this->id());
    }

    /**
     * @param array $config
     * @param array $arguments
     *
     * @return Queries\Query
     *
     * @throws \Deimos\QueryBuilder\Exceptions\NotFound
     */
    protected function manyToMany(array &$config, array &$arguments)
    {
        $table = $config['table'];
        $model = $config['model'];
        $from  = $config['from'];

        $pkModel = $this->orm->mapPK($model);
        $pkFrom  = $this->orm->mapPK($from);

        $leftRightModel = $config['modelId'] ?: $model . ucfirst($pkModel);
        $leftRightFrom  = $config['itemId'] ?: $from . ucfirst($pkFrom);

        return $this->orm->repository(['right' => $model])
            ->select(['right.*'])
            ->join(['leftRight' => $table])->inner()
            ->on('right.' . $pkModel, 'leftRight.' . $leftRightModel)
            ->where('leftRight.' . $leftRightFrom, $this->id());
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
            ->update();

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
     * @return array
     */
    public function getModify()
    {
        return $this->modify;   
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this);
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

    /**
     * @return string
     */
    protected static function modelName()
    {
        $orm   = StaticORM::getORM();
        $self  = new static($orm);
        $ref   = new \ReflectionClass($self);
        $short = $ref->getShortName();

        return lcfirst($short);
    }

    /**
     * @return string
     */
    protected static function primary()
    {
        return (new static(StaticORM::getORM()))
            ->primaryKey();
    }

    /**
     * @return Queries\Query
     */
    public static function query()
    {
        return StaticORM::getORM()
            ->repository(static::modelName());
    }

    /**
     * @param array $storage
     *
     * @return static
     *
     * @throws ModelNotLoad
     * @throws ModelNotModify
     */
    public static function create(array $storage)
    {
        return StaticORM::getORM()
            ->create(static::modelName(), $storage);
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public static function deleteById($id)
    {
        $orm = StaticORM::getORM();

        return (bool)$orm->database()->delete()
            ->from($orm->mapTable(static::modelName()))
            ->where(static::primary(), $id)
            ->delete();
    }

    /**
     * @return static
     */
    public static function first()
    {
        return static::query()->findOne();
    }

    /**
     * @return static
     */
    public static function last()
    {
        return static::query()
            ->orderBy(static::primary(), 'DESC')
            ->findOne();
    }

    /**
     * @param int $id
     *
     * @return static
     */
    public static function findById($id)
    {
        return static::query()
            ->where(static::primary(), $id)
            ->findOne();
    }

}
