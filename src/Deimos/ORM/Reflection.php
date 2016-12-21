<?php

namespace Deimos\ORM;

use Doctrine\Common\Inflector\Inflector;

class Reflection
{

    /**
     * @var \ReflectionClass[]
     */
    protected static $refClass = [];

    /**
     * @var \ReflectionProperty
     */
    protected static $refProperties = [];

    /**
     * @var array
     */
    protected static $tableList = [];

    /**
     * @var array
     */
    protected static $pkList = [];

    /**
     * @param string $class
     *
     * @return string
     */
    public static function getTableName($class)
    {
        if (!isset(static::$tableList[$class]))
        {
            if (!class_exists($class))
            {
                return $class;
            }

            $refProperty = static::getProperty($class, 'tableName');

            static::$tableList[$class] = $refProperty->getValue(new $class(null));

            if (!static::$tableList[$class])
            {
                $refClass  = static::reflectionClass($class);
                $class     = $refClass->getShortName();
                $modelName = lcfirst($class);

                static::$tableList[$class] = Inflector::pluralize($modelName);
            }
        }

        return static::$tableList[$class];
    }

    /**
     * @param string $class
     * @param string $name
     *
     * @return \ReflectionProperty
     */
    protected static function getProperty($class, $name)
    {
        if (!isset(static::$refProperties[$name]))
        {
            $refClass = static::reflectionClass($class);

            /**
             * @var $object \ReflectionProperty
             */
            static::$refProperties[$name] = $object = $refClass->getProperty($name);
            $object->setAccessible(true);
        }

        return static::$refProperties[$name];
    }

    /**
     * @param $class
     *
     * @return \ReflectionClass
     */
    protected static function reflectionClass($class)
    {
        if (!isset(static::$refClass[$class]))
        {
            static::$refClass[$class] = new \ReflectionClass($class);
        }

        return static::$refClass[$class];
    }

    /**
     * @param string $class
     *
     * @return string
     */
    public static function getPrimaryKey($class)
    {
        if (!isset(static::$pkList[$class]))
        {
            if (!class_exists($class))
            {
                return 'id';
            }

            $refProperty = static::getProperty($class, 'primaryKey');

            static::$pkList[$class] = $refProperty->getValue(new $class(null));
        }

        return static::$pkList[$class];
    }

    /**
     * @param Entity $object
     * @param string $tableName
     */
    public static function setTableName(Entity $object, $tableName)
    {
        $className = get_class($object);

        $refObject = static::getProperty($className, 'tableName');

        $refObject->setValue($object, $tableName);
    }

    /**
     * @param Entity $object
     * @param int    $state
     */
    public static function setState(Entity $object, $state)
    {
        $className = get_class($object);

        $refObject = static::getProperty($className, 'state');

        $refObject->setValue($object, $state);
    }

}