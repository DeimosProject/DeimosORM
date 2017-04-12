<?php

namespace Deimos\ORM;

use Doctrine\Common\Inflector\Inflector;

class Reflection
{

    /**
     * @var \ReflectionClass[]
     */
    protected $refClass = [];

    /**
     * @var \ReflectionProperty
     */
    protected $refProperties = [];

    /**
     * @var array
     */
    protected $tableList = [];

    /**
     * @var array
     */
    protected $pkList = [];

    /**
     * @param string $class
     *
     * @return string
     */
    public function getTableName($class)
    {
        if (!isset($this->tableList[$class]))
        {
            if (!class_exists($class))
            {
                return $class;
            }

            $reflectionClass = new \ReflectionClass($class);
//            $refProperty = $this->getProperty($class, 'tableName');

            $ref = $reflectionClass
                ->getProperty('tableName');
            $ref->setAccessible(true);

            $this->tableList[$class] =  $ref->getValue(new $class(null));

            if (!$this->tableList[$class])
            {
                $refClass  = $this->reflectionClass($class);
                $class     = $refClass->getShortName();
                $modelName = lcfirst($class);

                $this->tableList[$class] = Inflector::pluralize($modelName);
            }
        }

        return $this->tableList[$class];
    }

    /**
     * @param string $class
     * @param string $name
     *
     * @return \ReflectionProperty
     */
    protected function getProperty($class, $name)
    {
        if (!isset($this->refProperties[$name]))
        {
            $refClass = $this->reflectionClass($class);

            /**
             * @var $object \ReflectionProperty
             */
            $this->refProperties[$name] = $object = $refClass->getProperty($name);
            $object->setAccessible(true);
        }

        return $this->refProperties[$name];
    }

    /**
     * @param $class
     *
     * @return \ReflectionClass
     */
    protected function reflectionClass($class)
    {
        if (!isset($this->refClass[$class]))
        {
            $this->refClass[$class] = new \ReflectionClass($class);
        }

        return $this->refClass[$class];
    }

    /**
     * @param string $class
     *
     * @return string
     */
    public function getPrimaryKey($class)
    {
        if (!isset($this->pkList[$class]))
        {
            if (!class_exists($class))
            {
                return 'id';
            }

            $refProperty = $this->getProperty($class, 'primaryKey');

            $this->pkList[$class] = $refProperty->getValue(new $class(null));
        }

        return $this->pkList[$class];
    }

    /**
     * @param Entity $object
     * @param string $tableName
     */
    public function setTableName(Entity $object, $tableName)
    {
        $className = get_class($object);

        $refObject = $this->getProperty($className, 'tableName');

        $refObject->setValue($object, $tableName);
    }

    /**
     * @param Entity $object
     * @param int    $state
     */
    public function setState(Entity $object, $state)
    {
        $className = get_class($object);

        $refObject = $this->getProperty($className, 'state');

        $refObject->setValue($object, $state);
    }

}
