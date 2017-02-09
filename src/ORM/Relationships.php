<?php

namespace Deimos\ORM;

use Deimos\Config\ConfigObject;
use Doctrine\Common\Inflector\Inflector;

class Relationships
{

    /**
     * @var ConfigObject
     */
    protected $config;

    /**
     * @var array
     */
    protected $map = [];

    /**
     * @var []
     */
    protected $leftPluralize = [];

    /**
     * @var []
     */
    protected $rightPluralize = [
        'manyToMany',
    ];

    /**
     * @var string
     */
    protected $right;

    /**
     * @var string
     */
    protected $left;

    /**
     * @var bool
     */
    protected $init;

    /**
     * @param ConfigObject $config
     *
     * @return static
     */
    public function config(ConfigObject $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @param $modelName
     *
     * @return static
     */
    public function left($modelName)
    {
        $this->left = $modelName;

        return $this;
    }

    /**
     * @param $modelName
     *
     * @return static
     */
    public function right($modelName)
    {
        $this->right = $modelName;

        return $this;
    }

    protected function init()
    {
        $leftTable  = Inflector::pluralize($this->left);
        $rightTable = Inflector::pluralize($this->right);

        $type  = $this->config->get('type');
        $table = $this->config->get('table', $leftTable . ucfirst($rightTable));
        $left  = $this->config->get('left');

        if ($left === null)
        {
            $left = Inflector::singularize($this->right);
        }

        $leftId = $this->config->get('leftId');

        $right = $this->config->get('right');

        if ($right === null)
        {
            $right = Inflector::singularize($this->left);
        }

        $rightId = $this->config->get('rightId');

        $item = $this->right;
        if (in_array($type, $this->leftPluralize, true))
        {
            $item = Inflector::pluralize($this->right);
        }

        $this->map[$this->left] = [
            'type'    => $type,
            'table'   => $table,
            'model'   => $left,
            'modelId' => $leftId,
            'from'    => $this->left,
            'item'    => $item,
            'itemId'  => $rightId,
            'isLeft'  => 1
        ];

        $item = $right;
        if (in_array($type, $this->rightPluralize, true))
        {
            $item = Inflector::pluralize($item);
        }

        $this->map[$this->right] = [
            'type'    => $type,
            'table'   => $table,
            'model'   => $this->left,
            'modelId' => $rightId,
            'from'    => $left,
            'item'    => $item,
            'itemId'  => $leftId,
            'isLeft'  => 0
        ];

        $this->init = true;
    }

    /**
     * @return array
     */
    public function getLeft()
    {
        if (!$this->init)
        {
            $this->init();
        }

        return $this->map[$this->left];
    }

    /**
     * @return array
     */
    public function getRight()
    {
        if (!$this->init)
        {
            $this->init();
        }

        return $this->map[$this->right];
    }

}