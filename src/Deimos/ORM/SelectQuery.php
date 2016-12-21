<?php

namespace Deimos\ORM;

class SelectQuery extends Query
{

    /**
     * @var array
     */
    protected $defaults = [
        'fields' => '*'
    ];

    /**
     * @var array
     */
    protected $operators = [
        'fields'  => 'SELECT',
        'models'  => 'FROM',
        'join'    => '',
        'where'   => 'WHERE',
        'groupBy' => 'GROUP BY',
        'having'  => 'HAVING',
        'orderBy' => 'ORDER BY',
        'limit'   => 'LIMIT'
    ];

    /**
     * @return int
     */
    public function count()
    {
        $storage = (array)$this->storageFields;

        $this->storageFields = [];

        $sqlExpression = $this->builder->sqlExression('COUNT(1) as `count`');

        $this->fields($sqlExpression);

        $statement = $this->statementExec();

        $count = $statement->fetch(Connection::FETCH_ASSOC);

        $this->storageFields = $storage;

        return (int)$count['count'];
    }

    /**
     * @param bool $asArray
     *
     * @return Entity|array
     */
    public function findOne($asArray = false)
    {
        $limit = $this->storageLimit;

        $this->limit(1);
        $statement = $this->statementExec();

        $this->storageLimit = $limit;

        $model = $this->getCurrentClass();
        $table = $this->getTableName();

        if ($asArray)
        {
            return $statement->fetch(Connection::FETCH_ASSOC);
        }

        $object = $statement->fetchObject($model, [
            'builder'   => $this->builder,
            'state'     => Entity::STATE_QUERY,
            'tableName' => $table
        ]);

        Reflection::setState($object, Entity::STATE_LOADED);

        return $object;
    }

    /**
     * @param bool $asArray
     *
     * @return array
     */
    public function find($asArray = false)
    {
        $statement = $this->statementExec();

        $model = $this->getCurrentClass();
        $table = $this->getTableName();

        if ($asArray)
        {
            return $statement->fetchAll(Connection::FETCH_ASSOC);
        }

        $objects = $statement->fetchAll(Connection::FETCH_CLASS, $model, [
            'builder'   => $this->builder,
            'state'     => Entity::STATE_QUERY,
            'tableName' => $table
        ]);

        foreach ($objects as $object)
        {
            Reflection::setState($object, Entity::STATE_LOADED);
        }

        return $objects;
    }

    /**
     * @param string $sql
     * @param array  $parameters
     *
     * @return \PDOStatement
     */
    public function raw($sql, array $parameters = [])
    {
        return $this->statementExec($sql, $parameters);
    }

}