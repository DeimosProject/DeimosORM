<?php

namespace Deimos\ORM;

abstract class Query
{

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var array
     */
    protected $operators = [];

    /**
     * @var array
     */
    protected $defaults = [];

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @var string
     */
    protected $fields = '';
    /**
     * @var string
     */
    protected $models = '';

    /**
     * @var string
     */
    protected $join = '';

    /**
     * @var string
     */
    protected $orderBy = '';
    /**
     * @var string
     */
    protected $groupBy = '';

    /**
     * @var string
     */
    protected $where = '';
    /**
     * @var string
     */
    protected $having = '';

    /**
     * @var string
     */
    protected $set = '';

    /**
     * @var string
     */
    protected $limit = '';

    /**
     * @var bool
     */
    protected $allowOperator;

    /**
     * @var array
     */
    protected $storageFields = [];

    /**
     * @var array
     */
    protected $storageModels = [];

    /**
     * @var array
     */
    protected $storageJoin = [];

    /**
     * @var array
     */
    protected $storageWhere = [];

    /**
     * @var array
     */
    protected $storageHaving = [];

    /**
     * @var array
     */
    protected $storageGroupBy = [];

    /**
     * @var array
     */
    protected $storageOrderBy = [];

    /**
     * @var array
     */
    protected $storageSet = [];

    /**
     * @var int
     */
    protected $storageLimit;
    /**
     * @var int
     */
    protected $storageOffset;

    /**
     * @var array
     */
    protected $storageParameters = [];

    /**
     * Query constructor.
     *
     * @param $builder Builder
     */
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * ('id', 'name', [alias => value], ...)
     */
    public function fields(...$fields)
    {
        $this->storageFields = array_merge($this->storageFields, $fields);

        return $this;
    }

    /**
     * ('id', 'name', [alias => value], ...)
     */
    public function model(...$models)
    {
        $this->storageModels = array_merge($this->storageModels, $models);

        return $this;
    }

    /**
     * @param               $model
     * @param SQLExpression $expression
     *
     * @return static
     */
    public function join($model, SQLExpression $expression)
    {
        return $this->joinWithType($model, $expression);
    }

    /**
     * @param               $model
     * @param SQLExpression $expression
     * @param string        $type
     *
     * @return static
     */
    public function joinWithType($model, SQLExpression $expression, $type = '')
    {
        $this->setJoin($model, $expression, $type);

        return $this;
    }

    /**
     * @param               $model
     * @param SQLExpression $expression
     * @param               $type
     */
    protected function setJoin($model, $expression, $type)
    {
        $this->storageParameters = array_merge($this->storageParameters, $expression->getParameters());
        $this->storageJoin[]     = implode(' ', [
            $type . ' JOIN',
            $this->buildTable($model),
            'ON',
            $expression->getSQL()
        ]);
    }

    /**
     * @param $data
     *
     * @return string
     */
    protected function buildTable($data)
    {
        if (is_array($data))
        {
            $alias = key($data);
            $field = current($data);

            if (class_exists($field))
            {
                $field = Reflection::getTableName($field);
            }

            return $this->buildKey($field) . ' AS `' . $alias . '`';
        }

        if (class_exists($data))
        {
            $data = Reflection::getTableName($data);
        }

        return $this->buildKey($data);
    }

    /**
     * @param string|SQLExpression $key
     * @param bool                 $apostrophe
     *
     * @return string
     */
    protected function buildKey($key, $apostrophe = true)
    {
        if ($key instanceof SQLExpression)
        {
            return $this->buildSQLExpression($key);
        }

        if (!$apostrophe)
        {
            return $key;
        }

        $keys = explode('.', $key);

        foreach ($keys as $index => $value)
        {
            $keys[$index] = $this->gravis($value);
        }

        return implode('.', $keys);
    }

    /**
     * @param SQLExpression $sqlExpression
     *
     * @return string
     */
    protected function buildSQLExpression(SQLExpression $sqlExpression)
    {
        $this->parameters = array_merge($this->parameters, $sqlExpression->getParameters());

        return $sqlExpression->getSQL();
    }

    /**
     * @param $value
     *
     * @return string
     */
    protected function gravis($value)
    {
        return "`$value`";
    }

    /**
     * @param               $model
     * @param SQLExpression $expression
     *
     * @return static
     */
    public function joinRight($model, SQLExpression $expression)
    {
        return $this->joinWithType($model, $expression, 'RIGHT');
    }

    /**
     * @param               $model
     * @param SQLExpression $expression
     *
     * @return static
     */
    public function joinLeft($model, SQLExpression $expression)
    {
        return $this->joinWithType($model, $expression, 'LEFT');
    }

    /**
     * ('id', 'name', ...)
     *
     * @param array ...$where
     *
     * @return static
     */
    public function where(...$where)
    {
        $this->storageWhere[] = ['AND' => $where];

        return $this;
    }

    /**
     * @param $sql
     *
     * @return static
     */
    public function match($sql)
    {
        $exression = $this->builder->sqlExression('MATCH(?)', [$sql]);

        return $this->where($exression);
    }

    /**
     * ('id', 'name', ...)
     *
     * @param array ...$where
     *
     * @return static
     */
    public function whereOr(...$where)
    {
        $this->storageWhere[] = ['OR' => $where];

        return $this;
    }

    /**
     * ('id', 'name', ...)
     *
     * @param array ...$where
     *
     * @return static
     */
    public function whereXor(...$where)
    {
        $this->storageWhere[] = ['XOR' => $where];

        return $this;
    }

    /**
     * ('id', 'name', ...)
     *
     * @param array ...$having
     *
     * @return static
     */
    public function having(...$having)
    {
        $this->storageHaving[] = ['AND' => $having];

        return $this;
    }

    /**
     * ('id', 'name', ...)
     *
     * @param array ...$having
     *
     * @return static
     */
    public function havingOr(...$having)
    {
        $this->storageHaving[] = ['OR' => $having];

        return $this;
    }

    /**
     * ('id', 'name', ...)
     *
     * @param array ...$having
     *
     * @return static
     */
    public function havingXor(...$having)
    {
        $this->storageHaving[] = ['XOR' => $having];

        return $this;
    }

    /**
     * @param        $field
     * @param string $direction
     *
     * @return static
     */
    public function orderBy($field, $direction = 'ASC')
    {
        $this->storageOrderBy[] = $this->buildKey($field) . ' ' . $direction;

        return $this;
    }

    /**
     * @param $field
     *
     * @return static
     */
    public function groupBy($field)
    {
        $this->storageGroupBy[] = $this->buildKey($field);

        return $this;
    }

    /**
     * @param $limit
     *
     * @return static
     */
    public function take($limit)
    {
        return $this->limit($limit);
    }

    /**
     * @param $limit
     *
     * @return static
     */
    public function limit($limit)
    {
        $this->storageLimit = $limit;

        return $this;
    }

    /**
     * @param $offset
     *
     * @return static
     */
    public function skip($offset)
    {
        return $this->offset($offset);
    }

    /**
     * @param $offset
     *
     * @return static
     */
    public function offset($offset)
    {
        $this->storageOffset = $offset;

        return $this;
    }

    /**
     * @param array $storage
     *
     * @return static
     */
    public function setData(array $storage)
    {
        $this->storageSet = [];

        foreach ($storage as $column => $value)
        {
            $this->set($column, $value);
        }

        return $this;
    }

    /**
     * @param $column
     * @param $value
     *
     * @return static
     */
    public function set($column, $value)
    {
        $this->storageSet[] = $this->buildKey($column) . ' = ' . $this->buildValue($value);

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $sql              = [];
        $this->parameters = [];

        foreach ($this->operators as $name => $operator)
        {
            $ucFirst = ucfirst($name);

            $methodName = 'build' . $ucFirst;

            $storageName = 'storage' . $ucFirst;

            if (!empty($this->{$storageName}))
            {
                $this->{$methodName}();

                $sql[] = $operator . ' ' . $this->{$name};
            }
            else if (!empty($this->defaults[$name]))
            {
                $sql[] = $operator . ' ' . $this->defaults[$name];
            }
        }

        return implode(' ', $sql);
    }

    /**
     * @return array|mixed
     */
    protected function getCurrentClass()
    {
        $model = current($this->storageModels);

        if (is_array($model))
        {
            $model = current($model);
        }

        if (!class_exists($model))
        {
            return Entity::class;
        }

        return $model;
    }

    /**
     * @return array|mixed|string
     */
    protected function getTableName()
    {
        $model = current($this->storageModels);

        if (is_array($model))
        {
            $model = current($model);
        }

        if (!class_exists($model))
        {
            return $model;
        }

        return Reflection::getTableName($model);
    }

    /**
     * build join
     */
    protected function buildJoin()
    {
        $this->join = implode(' ', $this->storageJoin);
    }

    /**
     * build order by
     */
    protected function buildOrderBy()
    {
        $this->orderBy = implode(' ', $this->storageOrderBy);
    }

    /**
     * build group by
     */
    protected function buildGroupBy()
    {
        $this->groupBy = implode(' ', $this->storageGroupBy);
    }

    /**
     * build fields
     */
    protected function buildFields()
    {
        $fields = [];

        foreach ($this->storageFields as $fieldData)
        {
            if (is_array($fieldData))
            {
                $alias = key($fieldData);
                $field = current($fieldData);

                $fields[] = $this->buildKey($field) . ' AS `' . $alias . '`';
            }
            else
            {
                $fields[] = $this->buildKey($fieldData);
            }
        }

        $this->fields = implode(', ', $fields);
    }

    /**
     * build models
     */
    protected function buildModels()
    {
        $models = [];

        foreach ($this->storageModels as $modelData)
        {
            $models[] = $this->buildTable($modelData);
        }

        $this->models = implode(', ', $models);
    }

    /**
     * @param array ...$args
     *
     * @return string
     */
    protected function buildWhereOne(...$args) // 2 or 3 [1,1],
    {
        $result = $this->buildKey($args[0]);

        $count = count($args);

        if ($count === 3)
        {
            $result .= ' ' . $args[1] . ' ' . $this->buildValue($args[2]);
        }
        else if ($count === 2)
        {
            $value = $this->buildValue($args[1]);

            if ($value === 'NULL')
            {
                $result .= ' IS ' . $value;
            }
            else if (strpos($value, '(') === 0)
            {
                $result .= ' IN ' . $value;
            }
            else
            {
                $result .= ' = ' . $value;
            }
        }
        else if (!($args[0] instanceof SQLExpression))
        {
            throw new \InvalidArgumentException('Where');
        }

        return $result;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    protected function buildValue($value)
    {
        if (is_array($value))
        {
            return $this->buildInValue($value);
        }

        if ($value instanceof SQLExpression)
        {
            return $this->buildSQLExpression($value);
        }

        if (is_null($value))
        {
            return 'NULL';
        }

        $this->parameters[] = $value;

        return '?';
    }

    /**
     * @param array $parameters
     *
     * @return string
     */
    protected function buildInValue(array $parameters)
    {
        $string = str_repeat('?, ', count($parameters));

        $this->parameters = array_merge($this->parameters, $parameters);

        return '(' . rtrim($string, ', ') . ')';
    }

    /**
     * build where
     */
    protected function buildWhere()
    {
        /**
         * @var array $where
         */
        $where = $this->buildWhereOperator($this->storageWhere);

        $this->where         = '';
        $this->allowOperator = false;
        $this->buildIf2String([$where], $this->where);
    }

    /**
     * @param array  $args
     * @param string $defaultOperator
     *
     * @return string
     */
    protected function buildWhereOperator(array $args, $defaultOperator = 'AND')
    {
        $storage  = [];
        $key      = key($args);
        $operator = is_string($key) ? $key : $defaultOperator;

        foreach ($args as $arg)
        {
            $isArray = is_array(current($arg));

            if ($isArray)
            {
                $storage[] = $this->buildWhereOperator($arg, $operator);
            }
            else
            {
                $storage[] = [
                    $operator,
                    call_user_func_array([$this, 'buildWhereOne'], $arg)
                ];
            }
        }

        if (count($storage) === 1)
        {
            return current($storage);
        }

        return $storage;
    }

    /**
     * @param array  $storage
     * @param string $toStorage
     */
    protected function buildIf2String(array $storage, &$toStorage)
    {

        $toStorage .= '(';
        $lastOperator = '';

        foreach ($storage as $key => $value)
        {

            if (is_string($value[0]))
            {

                $this->allowOperator = true;
                $lastOperator        = $value[0];

                if ($key)
                {
                    $toStorage .= ' ' . $lastOperator . ' ';
                }

                $toStorage .= ' (' . $value[1] . ') ';
            }
            else
            {

                if ($this->allowOperator)
                {
                    $toStorage .= ' ' . $lastOperator . ' ';
                }

                $this->buildIf2String($value, $toStorage);
            }
        }

        $toStorage .= ')';

    }

    /**
     * build having
     */
    protected function buildHaving()
    {
        /**
         * @var array $having
         */
        $having = $this->buildWhereOperator($this->storageHaving);

        $this->having        = '';
        $this->allowOperator = false;
        $this->buildIf2String([$having], $this->having);
    }

    /**
     * build set
     */
    protected function buildSet()
    {
        $this->set = implode(', ', $this->storageSet);
    }

    /**
     * build limit
     */
    protected function buildLimit()
    {
        if ($this->storageLimit)
        {
            $this->limit = '';

            if ($this->storageOffset)
            {
                $this->limit = $this->storageOffset . ', ';
            }

            $this->limit .= $this->storageLimit;

            return;
        }

        if ($this->storageOffset)
        {
            throw new \InvalidArgumentException('Offset without limit!');
        }
    }

    /**
     * @param null|string $sql
     * @param array       $parameters
     *
     * @return \PDOStatement
     */
    protected function statementExec($sql = null, array $parameters = [])
    {
        $statement = $this->statement($sql);
        $statement->execute($parameters ? $parameters : $this->parameters());

        return $statement;
    }

    /**
     * @param null|string $sql
     *
     * @return \PDOStatement
     */
    protected function statement($sql = null)
    {
        static $statements = [];

        $connection = $this->builder->connection();

        /**
         * @var $sqlQuery string
         */
        $sqlQuery = $sql ?: (string)$this;

        if (empty($statements[$sqlQuery]))
        {
            $statements[$sqlQuery] = $connection->prepare($sqlQuery);
        }

        return $statements[$sqlQuery];
    }

    /**
     * @return array
     */
    public function parameters()
    {
        return array_merge($this->storageParameters, $this->parameters);
    }

}