<?php

namespace Deimos\ORM\Extension\Builder;

use Deimos\ORM\Config;
use Deimos\ORM\Constant\Relation as RelationConstant;
use Deimos\ORM\Entity;
use Deimos\ORM\Reflection;
use Deimos\ORM\SelectQuery;

/**
 * Class Relation
 *
 * @package Deimos\ORM\Extension\Builder
 * @method Reflection reflection()
 * @method Config config()
 */
trait Relation
{

    use RawQuery;

    /**
     * @var int
     */
    protected $type;

    /**
     * @var Reflection
     */
    protected $reflection;

    /**
     * @var Config
     */
    protected $config;

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
        $table      = $this->reflection()->getTableName($model);
        $this->type = $type;

        if ($type === RelationConstant::MANY2MANY)
        {
            return $this->relationMany2Many($entity, $table, $model);
        }

        if ($type === RelationConstant::ONE2MANY)
        {
            return $this->relationOne2Many($entity, $table, $model);
        }

        return $this->relationOne2One($entity, $table, $model);
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
    protected function relationMany2Many(Entity $entity, $model, $originModel)
    {
        $configModel = $this->config()->get($entity);
        $type        = $this->type;

        if (empty($configModel[$type][$model]))
        {
            throw new \InvalidArgumentException("Relation {$model} not found");
        }

        $data = $configModel[$type][$model];

        $expression = $this->sqlExpression("`right`.`{$data['currentPK']}` = `leftRight`.`{$data['currentKey']}`");

        return $this->queryEntity(['right' => $originModel])
            ->fields('right.*')
            ->join(['leftRight' => $data['tableName']], $expression)
            ->where('leftRight.' . $data['selfKey'], $entity->{$data['selfPK']});
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
        $configModel = $this->config()->get($entity);

        if (empty($configModel[RelationConstant::ONE2MANY][$model]))
        {
            throw new \InvalidArgumentException("Relation {$model} not found");
        }

        $relation = $configModel[RelationConstant::ONE2MANY][$model];

        return $this->queryEntity($originModel)
            ->where($relation['currentKey'], $entity->{$relation['selfKey']});
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
        return $this
            ->relationMany2Many($entity, $model, $originModel)
            ->limit(1);
    }

}