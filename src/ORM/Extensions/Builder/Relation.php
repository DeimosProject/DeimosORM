<?php

namespace Deimos\ORM\Extension\Builder;

use Deimos\ORM\Config;
use Deimos\ORM\Entity;
use Deimos\ORM\Reflection;
use Deimos\ORM\SelectQuery;
use Deimos\ORM\Сonstant\Relation as RelationConstant;

trait Relation
{

    use RawQuery;

    /**
     * @var int
     */
    protected $type;

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

        if ($type === RelationConstant::MANY2MANY)
        {
            $this->type = RelationConstant::MANY2MANY;

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
        $configModel = Config::get($entity);
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
        $configModel = Config::get($entity);

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
        $this->type = RelationConstant::ONE2ONE;

        return $this
            ->relationMany2Many($entity, $model, $originModel)
            ->limit(1);
    }

}