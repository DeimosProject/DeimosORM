<?php

namespace Deimos\ORM;

use Deimos\Builder\Builder;
use Deimos\Database\Database;
use Deimos\Helper\Helper;
use Deimos\Slice\Slice;
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
     * @var string[]
     */
    protected $pkMap = [];

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
     * @param Helper   $helper
     * @param Database $database
     */
    public function __construct(Helper $helper, Database $database)
    {
        $this->helper   = $helper;
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
     * @param array $storage
     *
     * @return static
     */
    public function setConfig(array $storage)
    {
        foreach ($storage as $modelName => $config)
        {
            $class     = isset($config['class']) ? $config['class'] : Entity::class;
            $relations = isset($config['relations']) ? $config['relations'] : [];

            $this->register($modelName, $class, $relations);
        }

        return $this;
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
            $object = new Slice($this->helper, $config);

            $type = $object->getRequired('type');

            $relation = $this->relationships($type)
                ->config($object)
                ->left($left)
                ->right($right);

            $map = [
                $relation->getLeft(),
                $relation->getRight()
            ];

            foreach ($map as $self)
            {
                $item = $self['item'];
                $from = $self['from'];

                if (!isset($this->configMap[$from]))
                {
                    $this->configMap[$from] = [$item => $self];
                }
                else
                {
                    $this->configMap[$from][$item] = $self;
                }

            }
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
    public function mapPK($modelName)
    {
        if (!isset($this->pkMap[$modelName]))
        {
            $this->mapTable($modelName);
        }

        return $this->pkMap[$modelName];
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

            $this->pkMap[$modelName] = $object->primaryKey();

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
     *
     * @throws Exceptions\ModelNotLoad
     * @throws Exceptions\ModelNotModify
     */
    public function create($modelName, array $storage = null)
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

        if ($storage)
        {
            $object->save($storage);
        }

        return $object;
    }

}
