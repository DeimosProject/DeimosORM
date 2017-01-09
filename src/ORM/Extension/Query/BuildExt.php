<?php

namespace Deimos\ORM\Extension\Query;

use Deimos\ORM\SQLExpression;

trait BuildExt
{

    use Statement;

    /**
     * @var bool
     */
    protected $allowOperator;

    /**
     * @param array ...$args
     *
     * @return string
     *
     * @throws \InvalidArgumentException
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

        if ($value === null)
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

}