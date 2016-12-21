<?php

namespace Deimos\ORM;

class Entity extends \stdClass
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

    public function isLoaded()
    {
        return $this->state === self::STATE_LOADED;
    }

    public function isCreated()
    {
        return $this->state === self::STATE_CREATED;
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
     * @param string $model
     * @param string $type
     *
     * @return SelectQuery
     */
    public function relation($model, $type)
    {
        return $this->builder->relation($this, $model, $type);
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
     * @return bool
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

    protected function modify2Origin()
    {
        $this->storageOrigin = array_merge($this->storageOrigin, $this->storageModify);
        $this->storageModify = [];
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
                ->where($primaryKey, $this->{$primaryKey})
                ->setData($this->storageModify)
                ->updateOne();

            $this->modify2Origin();

            return $update;
        }

        $id = $this->builder->create()
            ->model($this->tableName())
            ->setData($this->storageModify)
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
     * @param $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->storageOrigin);
    }

}