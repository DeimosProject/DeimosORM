<?php

namespace Deimos\ORM;

use Doctrine\Common\Inflector\Inflector;

class Config
{

    /**
     * @var array
     */
    protected static $storage;

    protected static $modelConfig;

    public static function setStorage(array $storage)
    {
        static::$storage = $storage;
    }

    public static function get(Entity $entity)
    {
        if (!static::$modelConfig)
        {
            static::init();
        }
//        var_dump(static::$modelConfig);
        $entityClass = get_class($entity);

        $key = Reflection::getTableName($entityClass);

        return isset(static::$modelConfig[$key]) ?
            static::$modelConfig[$key] : [];
    }

    protected static function getProxyRequired($value, $key)
    {
        if (array_key_exists($key, $value))
        {
            return $value[$key];
        }

        throw new \InvalidArgumentException("Required key '{$key}' not found");
    }

    protected static function getProxy($value, $key, $default = null)
    {
        try
        {
            $data = static::getProxyRequired($value, $key);
        }
        catch (\Exception $exception)
        {
            $data = $default;
        }

        return $data;
    }

    protected static function initOneToMany($value, $type)
    {
        $ownerObject = static::getProxyRequired($value, 'owner');
        $itemsObject = static::getProxyRequired($value, 'items');

        $owner = Reflection::getTableName($ownerObject);
        $items = Reflection::getTableName($itemsObject);

        $ownerPK = Reflection::getPrimaryKey($ownerObject);
        $itemsPK = Reflection::getPrimaryKey($itemsObject);

        $singularizeOwner = Inflector::singularize($owner);

        $itemsKey = static::getProxy($value, 'itemsKey', $singularizeOwner . ucfirst($ownerPK));

        static::$modelConfig[$owner][$type][$items] = [
            'tableName'  => $items,
            'currentKey' => $itemsKey,
            'selfKey'    => $itemsPK,
        ];

        static::$modelConfig[$items][$type][$owner] = [
            'tableName'  => $owner,
            'currentKey' => $itemsPK,
            'selfKey'    => $itemsKey,
        ];

    }

    protected static function initGMany($value, $type)
    {
        $rightObject = static::getProxyRequired($value, 'right');
        $leftObject  = static::getProxyRequired($value, 'left');

        $right = Reflection::getTableName($rightObject);
        $left  = Reflection::getTableName($leftObject);

        $rightPK = Reflection::getPrimaryKey($rightObject);
        $leftPK  = Reflection::getPrimaryKey($leftObject);

        $defaultTable = $left . ucfirst($right);
        $tableName    = static::getProxy($value, 'tableName', $defaultTable);

        $singularizeRight = Inflector::singularize($right);
        $singularizeLeft  = Inflector::singularize($left);

        $rightKey = static::getProxy($value, 'rightKey', $singularizeRight . ucfirst($rightPK));
        $leftKey  = static::getProxy($value, 'leftKey', $singularizeLeft . ucfirst($leftPK));

        static::$modelConfig[$right][$type][$left] = [
            'model'      => $singularizeLeft,
            'tableName'  => $tableName,
            'currentPK'  => $leftPK,
            'currentKey' => $leftKey,
            'selfPK'     => $rightPK,
            'selfKey'    => $rightKey
        ];

        static::$modelConfig[$left][$type][$right] = [
            'model'      => $singularizeRight,
            'tableName'  => $tableName,
            'currentPK'  => $rightPK,
            'currentKey' => $rightKey,
            'selfPK'     => $leftPK,
            'selfKey'    => $leftKey
        ];
    }

    protected static function init()
    {
        foreach (static::$storage as $value)
        {

            $type = static::getProxyRequired($value, 'type');

            if (in_array($type, [Builder::MANY2MANY, Builder::ONE2ONE], true))
            {
                static::initGMany($value, $type);
            }
            else
            {
                static::initOneToMany($value, $type);
            }

        }
    }

}