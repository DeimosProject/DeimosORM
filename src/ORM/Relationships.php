<?php

namespace Deimos\ORM;

use Deimos\Slice\Slice;
use Doctrine\Common\Inflector\Inflector;

class Relationships
{

    /**
     * @var Slice
     */
    protected $slice;

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
     * @param Slice $slice
     *
     * @return static
     */
    public function config(Slice $slice)
    {
        $this->slice = $slice;

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

        $type  = $this->slice->getData('type');
        $table = $this->slice->getData('table', $leftTable . ucfirst($rightTable));
        $left  = $this->slice->getData('left');

        if ($left === null)
        {
            $left = Inflector::singularize($this->right);
        }

        $leftId = $this->slice->getData('leftId');

        $right = $this->slice->getData('right');

        if ($right === null)
        {
            $right = Inflector::singularize($this->left);
        }

        $rightId = $this->slice->getData('rightId');

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
        $this->init OR $this->init();

        return $this->map[$this->left];
    }

    /**
     * @return array
     */
    public function getRight()
    {
        $this->init OR $this->init();

        return $this->map[$this->right];
    }

}