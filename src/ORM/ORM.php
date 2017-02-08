<?php

namespace Deimos\ORM;

use Deimos\Builder\Builder;
use Deimos\Config\ConfigObject;
use Deimos\Database\Database;
use Doctrine\Common\Inflector\Inflector;

class ORM
{

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var string[]
     */
    protected $classMap = [];

    /**
     * @var string[]
     */
    protected $tableMap = [];

    /**
     * @var []
     */
    protected $configMap = [];

    /**
     * @var Relationships[]
     */
    protected $relationMap = [
        'oneToMany'  => Relationships\OneToMany::class,
        'manyToMany' => Relationships\ManyToMany::class,
    ];

    /**
     * ORM constructor.
     *
     * @param Builder  $builder
     * @param Database $database
     */
    public function __construct(Builder $builder, Database $database)
    {
        $this->builder  = $builder;
        $this->database = $database;
    }

    /**
     * @return Database
     */
    public function database()
    {
        return $this->database;
    }

    /**
     * @param string $modelName
     *
     * @return array
     */
    public function config($modelName)
    {
        if (!isset($this->configMap[$modelName]))
        {
            return null;
        }

        return $this->configMap[$modelName];
    }

    /**
     * @param string $modelName
     * @param string $class
     * @param array  $config
     */
    public function register($modelName, $class, array $config = null)
    {
        $this->classMap[$modelName] = $class;

        if (is_array($config))
        {
            $this->registerConfig($modelName, $config);
        }
    }

    protected function registerConfig($left, array $allConfig)
    {
        foreach ($allConfig as $right => $config)
        {
            $object = new ConfigObject($this->builder, $config);

            $type = $object->getRequired('type');

            $relation = $this->relationships($type)
                ->config($object)
                ->left($left)
                ->right($right);

            $this->configMap[$left]  = $relation->getLeft();
            $this->configMap[$right] = $relation->getRight();
        }
    }

    /**
     * @param string $type
     *
     * @return Relationships
     */
    protected function relationships($type)
    {
        $relationClass = $this->relationMap[$type];

        return new $relationClass();
    }

    /**
     * @param string $modelName
     *
     * @return string
     */
    public function mapClass($modelName)
    {
        if (isset($this->classMap[$modelName]))
        {
            return $this->classMap[$modelName];
        }

        return Entity::class;
    }

    /**
     * @param string $modelName
     *
     * @return string
     */
    public function mapTable($modelName)
    {
        if (!isset($this->tableMap[$modelName]))
        {
            $class = $this->mapClass($modelName);

            /**
             * @var $object Entity
             */
            $object = new $class($this);

            $this->tableMap[$modelName] =
                $object->tableName() ?:
                    Inflector::pluralize($modelName);
        }

        return $this->tableMap[$modelName];
    }

    /**
     * @param string $modelName
     *
     * @return Queries\Query
     */
    public function repository($modelName)
    {
        return new Queries\Query(
            $this,
            $modelName
        );
    }

    /**
     * @param string $modelName
     *
     * @return Entity
     */
    public function create($modelName)
    {
        $class = $this->mapClass($modelName);

        /**
         * @var Entity $object
         */
        $object = new $class(
            $this,
            true,
            $this->mapTable($modelName)
        );

        $object->setModelName($modelName);

        return $object;
    }

}
