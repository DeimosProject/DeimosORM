<?php

namespace Deimos\ORM;

class Entity implements \JsonSerializable
{

    const STATE_CREATED = 0;
    const STATE_LOADED  = 1;
    const STATE_QUERY   = 2;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var array
     */
    protected $storageOrigin = [];

    /**
     * @var array
     */
    protected $storageModify = [];

    /**
     * @var int
     */
    protected $state;

    /**
     * Entity constructor.
     *
     * @param Builder $builder
     * @param int     $state
     * @param null    $tableName
     */
    public function __construct($builder, $state = self::STATE_CREATED, $tableName = null)
    {
        $this->builder = $builder;
        $this->state   = $state;

        if ($tableName)
        {
            $this->tableName = $tableName;
        }
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->storageModify[$name]))
        {
            return $this->storageModify[$name];
        }

        return $this->getOrigin($name);
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        if ($this->isLoaded() || $this->isCreated())
        {
            $this->storageModify[$name] = $value;
        }
        else
        {
            $this->storageOrigin[$name] = $value;
        }
    }

    /**
     * @return int
     */
    public function id()
    {
        return $this->{$this->primaryKey};
    }

    /**
     * @return bool
     */
    public function isLoaded()
    {
        return $this->state === self::STATE_LOADED;
    }

    /**
     * @return bool
     */
    public function isCreated()
    {
        return $this->state === self::STATE_CREATED;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function getOrigin($name)
    {
        return $this->storageOrigin[$name];
    }

    /**
     * @param string $model
     * @param string $type
     *
     * @return SelectQuery
     *
     * @throws \InvalidArgumentException
     */
    protected function relation($model, $type)
    {
        return $this->builder->relation($this, $model, $type);
    }

    /**
     * @return bool
     *
     * @throws \InvalidArgumentException
     */
    public function delete()
    {
        if (!$this->isLoaded())
        {
            throw new \InvalidArgumentException('Model is not load');
        }

        $primaryKey = $this->primaryKey;

        $isDelete = $this->builder->delete()
            ->model($this->tableName)
            ->where($primaryKey, $this->{$primaryKey})
            ->deleteOne();

        if ($isDelete)
        {
            unset($this->storageOrigin[$primaryKey]);
            $this->state = self::STATE_CREATED;
        }

        return $isDelete;
    }

    /**
     * @return bool
     */
    public function save()
    {
        $primaryKey = $this->primaryKey;

        if ($this->isLoaded())
        {
            $update = $this->builder->updateEntity($this->tableName())
                ->setData($this->modifyAsArray())
                ->where($primaryKey, $this->{$primaryKey})
                ->updateOne();

            $this->modify2Origin();

            return $update;
        }

        $id = $this->builder->create()
            ->model($this->tableName())
            ->setData($this->modifyAsArray())
            ->insert();

        if ($id)
        {
            $this->storageModify[$primaryKey] = $id;
            $this->modify2Origin();

            $this->state = self::STATE_LOADED;
        }

        return $id > 0;
    }

    /**
     * @return string
     */
    protected function tableName()
    {
        if ($this->isLoaded())
        {
            return $this->tableName;
        }

        return Reflection::getTableName(static::class);
    }

    protected function modify2Origin()
    {
        $this->storageOrigin = $this->asArray();
        $this->storageModify = [];
    }

    /**
     * @return array
     */
    public function asArray()
    {
        return array_merge($this->originAsArray(), $this->modifyAsArray());
    }

    /**
     * @return array
     */
    public function originAsArray()
    {
        return $this->storageOrigin;
    }

    /**
     * @return array
     */
    public function modifyAsArray()
    {
        return $this->storageModify;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->asArray();
    }

    /**
     * @param $value
     */
    public function __invoke($value)
    {
        $this->state = $value;
    }

    /**
     * @param $model
     *
     * @return SelectQuery
     *
     * @throws \InvalidArgumentException
     */
    public function manyToMany($model)
    {
        return $this->relation($model, Builder::MANY2MANY);
    }

    /**
     * @param $model
     *
     * @return SelectQuery
     *
     * @throws \InvalidArgumentException
     */
    public function oneToOne($model)
    {
        return $this->relation($model, Builder::ONE2ONE);
    }

    /**
     * @param $model
     *
     * @return SelectQuery
     *
     * @throws \InvalidArgumentException
     */
    public function oneToMany($model)
    {
        return $this->relation($model, Builder::ONE2MANY);
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->storageOrigin[$name]);
    }

}