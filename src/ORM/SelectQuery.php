<?php

namespace Deimos\ORM;

use Deimos\ORM\Extension\Query\Field;
use Deimos\ORM\Extension\Query\GroupBy;
use Deimos\ORM\Extension\Query\Having;
use Deimos\ORM\Extension\Query\Join;
use Deimos\ORM\Extension\Query\Limit;
use Deimos\ORM\Extension\Query\Offset;
use Deimos\ORM\Extension\Query\Option;
use Deimos\ORM\Extension\Query\OrderBy;
use Deimos\ORM\Extension\Query\Where;

class SelectQuery extends Query
{

    use Field;
    use Join;
    use Where;
    use GroupBy;
    use Having;
    use OrderBy;
    use Limit;
    use Offset;
    use Option;

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
        'limit'   => 'LIMIT',
        'option'  => 'OPTION',
    ];

    /**
     * @return int
     */
    public function count()
    {
        $storage = (array)$this->storageFields;

        $this->storageFields = [];

        $sqlExpression = $this->builder->sqlExpression('COUNT(1) as `count`');

        $this->fields($sqlExpression);

        $statement = $this->statementExec();

        $count = $statement->fetch(Connection::FETCH_ASSOC);

        $this->storageFields = $storage;

        return (int)$count['count'];
    }

    /**
     * @param bool $asObject
     *
     * @return Entity|array
     */
    public function findOne($asObject = true)
    {
        $limit = $this->storageLimit;

        $this->limit(1);
        $statement = $this->statementExec();

        $this->storageLimit = $limit;

        $model = $this->getCurrentClass();
        $table = $this->getTableName();

        if ($asObject)
        {
            $object = $statement->fetchObject($model, [
                'builder'   => $this->builder,
                'state'     => Entity::STATE_QUERY,
                'tableName' => $table
            ]);
            
            if (!$object)
            {
                $object = new $model(
                    $this->builder,
                    Entity::STATE_CREATED,
                    $table
                );
            }
            else
            {
                $object(Entity::STATE_LOADED);
            }
            
            return $object;
        }

        return $statement->fetch(Connection::FETCH_ASSOC);
    }

    /**
     * @param bool $asObject
     *
     * @return array
     */
    public function find($asObject = true)
    {
        $statement = $this->statementExec();

        $model = $this->getCurrentClass();
        $table = $this->getTableName();

        if ($asObject)
        {
            $objects = $statement->fetchAll(Connection::FETCH_CLASS, $model, [
                'builder'   => $this->builder,
                'state'     => Entity::STATE_QUERY,
                'tableName' => $table
            ]);

            foreach ($objects as $object)
            {
                $object(Entity::STATE_LOADED);
            }

            return $objects;
        }

        return $statement->fetchAll(Connection::FETCH_ASSOC);
    }

    /**
     * @param string $sql
     * @param array  $parameters
     *
     * @return \PDOStatement
     */
    public function raw($sql, array $parameters = [])
    {
        $statement = $this->statementExec($sql, $parameters);
        $statement->closeCursor();

        return $statement;
    }

}
