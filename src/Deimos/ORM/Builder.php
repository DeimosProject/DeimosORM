<?php

namespace Deimos\ORM;

class Builder
{

    const MANY2MANY = 'manyToMany';
    const ONE2MANY  = 'oneToMany';
    const ONE2ONE   = 'oneToOne';

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var array
     */
    protected $options = [
        'query'  => SelectQuery::class,
        'insert' => InsertQuery::class,
        'update' => UpdateQuery::class,
        'delete' => DeleteQuery::class,
    ];

    /**
     * Builder constructor.
     *
     * @param string $name
     */
    public function __construct($name = null)
    {
        if ($name)
        {
            $this->setConnection($name);
        }
    }

    public function setConnection($name = 'default')
    {
        $this->connection = Connection::get($name);
    }

    /**
     * @return Connection
     */
    public function connection()
    {
        if (!$this->connection)
        {
            $this->setConnection();
        }

        return $this->connection;
    }

    /**
     * @param       $sql
     * @param array $parameters
     *
     * @return \PDOStatement
     */
    public function rawQuery($sql, array $parameters = [])
    {
        return $this->query()->raw($sql, $parameters);
    }

    /**
     * @return SelectQuery
     */
    public function query()
    {
        $query = $this->options['query'];

        return new $query($this);
    }

    /**
     * @param Entity $entity
     * @param string $model
     * @param string $type
     *
     * @return SelectQuery
     *
     * @throws \InvalidArgumentException
     */
    public function relation(Entity $entity, $model, $type)
    {
        $table = Reflection::getTableName($model);

        if ($type === static::MANY2MANY)
        {
            return $this->relationMany2Many($entity, $table, $model);
        }

        if ($type === static::ONE2MANY)
        {
            return $this->relationOne2Many($entity, $table, $model);
        }

        return $this->relationOne2One($entity, $table, $model);
    }

    /**
     * @param Entity $entity
     * @param string $model
     * @param string $originModel
     * @param string $type
     *
     * @return SelectQuery
     *
     * @throws \InvalidArgumentException
     */
    protected function relationMany2Many(Entity $entity, $model, $originModel, $type = self::MANY2MANY)
    {
        $configModel = Config::get($entity);

        if (empty($configModel[$type][$model]))
        {
            throw new \InvalidArgumentException("Relation {$model} not found");
        }

        $data = $configModel[$type][$model];

        $exression = $this->sqlExression("`right`.`{$data['currentPK']}` = `leftRight`.`{$data['currentKey']}`");

        return $this->queryEntity(['right' => $originModel])
            ->fields('right.*')
            ->join(['leftRight' => $data['tableName']], $exression)
            ->where('leftRight.' . $data['selfKey'], $entity->{$data['selfPK']});
    }

    /**
     * @param       $sql
     * @param array $parameters
     *
     * @return SQLExpression
     */
    public function sqlExression($sql, array $parameters = [])
    {
        return new SQLExpression($sql, $parameters);
    }

    /**
     * @param $class
     *
     * @return SelectQuery
     */
    public function queryEntity($class)
    {
        return $this->query()->model($class);
    }

    /**
     * @param Entity $entity
     * @param string $model
     * @param string $originModel
     *
     * @return SelectQuery
     *
     * @throws \InvalidArgumentException
     */
    protected function relationOne2Many(Entity $entity, $model, $originModel)
    {
        $configModel = Config::get($entity);

        if (empty($configModel[static::ONE2MANY][$model]))
        {
            throw new \InvalidArgumentException("Relation {$model} not found");
        }

        $data = $configModel[static::ONE2MANY][$model];

        return $this->queryEntity($originModel)
            ->where($data['currentKey'], $entity->{$data['selfKey']});
    }

    /**
     * @param Entity $entity
     * @param string $model
     * @param string $originModel
     *
     * @return SelectQuery
     *
     * @throws \InvalidArgumentException
     */
    protected function relationOne2One(Entity $entity, $model, $originModel)
    {
        return $this->relationMany2Many($entity, $model, $originModel, static::ONE2ONE)->limit(1);
    }

    /**
     * @return InsertQuery
     */
    public function create()
    {
        $create = $this->options['insert'];

        return new $create($this);
    }

    /**
     * @param $class
     *
     * @return Entity
     */
    public function createEntity($class)
    {
        $tableName = Reflection::getTableName($class); // todo

        return new $class($this, Entity::STATE_CREATED, $tableName);
    }

    /**
     * @param $class
     *
     * @return UpdateQuery
     */
    public function updateEntity($class)
    {
        return $this->update()->model($class);
    }

    /**
     * @return UpdateQuery
     */
    public function update()
    {
        $update = $this->options['update'];

        return new $update($this);
    }

    /**
     * @param $class
     *
     * @return DeleteQuery
     */
    public function deleteEntity($class)
    {
        return $this->delete()->model($class);
    }

    /**
     * @return DeleteQuery
     */
    public function delete()
    {
        $delete = $this->options['delete'];

        return new $delete($this);
    }

}