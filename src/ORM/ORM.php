<?php

namespace Deimos\ORM;

use Deimos\Database\Database;
use Doctrine\Common\Inflector\Inflector;

class ORM
{

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var []
     */
    protected $configure = [];

    /**
     * @var []
     */
    protected $classMap = [];

    /**
     * @var []
     */
    protected $tableMap = [];

    /**
     * ORM constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * @param string $modelName
     * @param string $class
     */
    public function register($modelName, $class)
    {
        $this->classMap[$modelName] = $class;
    }

    /**
     * @param $modelName
     *
     * @return mixed
     */
    protected function mapClass($modelName)
    {
        if (isset($this->classMap[$modelName]))
        {
            return $this->classMap[$modelName];
        }

        return Entity::class;
    }

    /**
     * @param $modelName
     *
     * @return mixed
     */
    protected function mapTable($modelName)
    {
        if (!isset($this->tableMap[$modelName]))
        {
            $class = $this->mapClass($modelName);

            /**
             * @var $object Entity
             */
            $object = new $class(null);

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
            $this->database,
            $this->mapClass($modelName),
            $this->mapTable($modelName)
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

        return new $class(
            $this->database,
            true,
            $this->mapTable($modelName)
        );
    }

}
