<?php

namespace Deimos\ORM;

use Deimos\ORM\Constant\Relation;
use Doctrine\Common\Inflector\Inflector;

class Config
{

    /**
     * @var array
     */
    protected $storage;

    /**
     * @var array
     */
    protected $modelConfig;

    /**
     * @var Reflection
     */
    protected $reflection;

    /**
     * Config constructor.
     *
     * @param Reflection $reflection
     */
    public function __construct(Reflection $reflection)
    {
        $this->reflection = $reflection;
    }

    /**
     * @param array $storage
     */
    public function setStorage(array $storage)
    {
        $this->modelConfig = null;
        $this->storage     = $storage;
    }

    /**
     * @param Entity $entity
     *
     * @return array|mixed
     * @throws \InvalidArgumentException
     */
    public function get(Entity $entity)
    {
        if (!$this->modelConfig)
        {
            $this->init();
        }

        $entityClass = get_class($entity);

        $key = $this->reflection->getTableName($entityClass);

        return isset($this->modelConfig[$key]) ?
            $this->modelConfig[$key] : [];
    }

    /**
     * init config
     *
     * @throws \InvalidArgumentException
     */
    protected function init()
    {
        foreach ($this->storage as $value)
        {

            $type = $this->getProxyRequired($value, 'type');

            if (in_array($type, [Relation::MANY2MANY, Relation::ONE2ONE], true))
            {
                $this->initGMany($value, $type);
            }
            else
            {
                $this->initOneToMany($value, $type);
            }

        }
    }

    /**
     * @param $value
     * @param $key
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    protected function getProxyRequired($value, $key)
    {
        if (array_key_exists($key, $value))
        {
            return $value[$key];
        }

        throw new \InvalidArgumentException("Required key '{$key}' not found");
    }

    /**
     * @param $value
     * @param $type
     *
     * @throws \InvalidArgumentException
     */
    protected function initGMany($value, $type)
    {
        $rightObject = $this->getProxyRequired($value, 'right');
        $leftObject  = $this->getProxyRequired($value, 'left');

        $right = $this->reflection->getTableName($rightObject);
        $left  = $this->reflection->getTableName($leftObject);

        $rightPK = $this->reflection->getPrimaryKey($rightObject);
        $leftPK  = $this->reflection->getPrimaryKey($leftObject);

        $defaultTable = $left . ucfirst($right);
        $tableName    = $this->getProxy($value, 'tableName', $defaultTable);

        $singularRight = Inflector::singularize($right);
        $singularLeft  = Inflector::singularize($left);

        $rightKey = $this->getProxy($value, 'rightKey', $singularRight . ucfirst($rightPK));
        $leftKey  = $this->getProxy($value, 'leftKey', $singularLeft . ucfirst($leftPK));

        $this->modelConfig[$right][$type][$left] = [
            'model'      => $singularLeft,
            'tableName'  => $tableName,
            'currentPK'  => $leftPK,
            'currentKey' => $leftKey,
            'selfPK'     => $rightPK,
            'selfKey'    => $rightKey
        ];

        $this->modelConfig[$left][$type][$right] = [
            'model'      => $singularRight,
            'tableName'  => $tableName,
            'currentPK'  => $rightPK,
            'currentKey' => $rightKey,
            'selfPK'     => $leftPK,
            'selfKey'    => $leftKey
        ];
    }

    /**
     * @param      $value
     * @param      $key
     * @param null $default
     *
     * @return mixed|null
     */
    protected function getProxy($value, $key, $default = null)
    {
        try
        {
            $data = $this->getProxyRequired($value, $key);
        }
        catch (\Exception $exception)
        {
            $data = $default;
        }

        return $data;
    }

    /**
     * @param $value
     * @param $type
     */
    protected function initOneToMany($value, $type)
    {
        $ownerObject = $this->getProxyRequired($value, 'owner');
        $itemsObject = $this->getProxyRequired($value, 'items');

        $owner = $this->reflection->getTableName($ownerObject);
        $items = $this->reflection->getTableName($itemsObject);

        $ownerPK = $this->reflection->getPrimaryKey($ownerObject);
        $itemsPK = $this->reflection->getPrimaryKey($itemsObject);

        $singularOwner = Inflector::singularize($owner);

        $itemsKey = $this->getProxy($value, 'itemsKey', $singularOwner . ucfirst($ownerPK));

        $this->modelConfig[$owner][$type][$items] = [
            'tableName'  => $items,
            'currentKey' => $itemsKey,
            'selfKey'    => $itemsPK,
        ];

        $this->modelConfig[$items][$type][$owner] = [
            'tableName'  => $owner,
            'currentKey' => $itemsPK,
            'selfKey'    => $itemsKey,
        ];

    }

}