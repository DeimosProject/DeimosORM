<?php

namespace Deimos\ORM\Extension\Query;

use Deimos\ORM\Entity;

/**
 * Class Model
 *
 * @package Deimos\ORM\Extension\Query
 */
trait Model
{

    /**
     * @var string
     */
    protected $models = '';

    /**
     * @var array
     */
    protected $storageModels = [];

    /**
     * ('id', 'name', [alias => value], ...)
     *
     * @param array ...$models
     *
     * @return static
     */
    public function model(...$models)
    {
        $this->storageModels = array_merge($this->storageModels, $models);

        return $this;
    }

    /**
     * build models
     */
    protected function buildModels()
    {
        $models = [];

        foreach ($this->storageModels as $modelData)
        {
            $models[] = $this->buildTable($modelData);
        }

        $this->models = implode(', ', $models);
    }

    /**
     * @param $data
     *
     * @return string
     */
    protected function buildTable($data)
    {
        if (is_array($data))
        {
            $alias = key($data);
            $field = current($data);

            return $this->refTable($field) . ' AS `' . $alias . '`';
        }

        return $this->refTable($data);
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    protected function refTable($value)
    {
        if (class_exists($value))
        {
            $value = $this->builder->reflection()->getTableName($value);
        }

        return $this->buildKey($value);
    }

    /**
     * @return array|mixed
     */
    protected function getCurrentClass()
    {
        $model = current($this->storageModels);

        if (is_array($model))
        {
            $model = current($model);
        }

        if (!class_exists($model))
        {
            return Entity::class;
        }

        return $model;
    }

    /**
     * @return array|mixed|string
     */
    protected function getTableName()
    {
        $model = current($this->storageModels);

        if (is_array($model))
        {
            $model = current($model);
        }

        if (!class_exists($model))
        {
            return $model;
        }

        return $this->builder->reflection()->getTableName($model);
    }

}