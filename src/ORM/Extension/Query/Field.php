<?php

namespace Deimos\ORM\Extension\Query;

/**
 * Class Field
 *
 * @package Deimos\ORM\Extension\Query
 */
trait Field
{

    /**
     * @var string
     */
    protected $fields = '';

    /**
     * @var array
     */
    protected $storageFields = [];

    /**
     * ('id', 'name', [alias => value], ...)
     *
     * @param array ...$fields
     *
     * @return static
     */
    public function fields(...$fields)
    {
        $this->storageFields = array_merge($this->storageFields, $fields);

        return $this;
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

}