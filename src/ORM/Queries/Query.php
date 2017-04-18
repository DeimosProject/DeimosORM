<?php

namespace Deimos\ORM\Queries;

use Deimos\Database\Connection;
use Deimos\ORM\Entity;
use Deimos\ORM\ORM;

class Query extends \Deimos\Database\Queries\Query
{

    /**
     * @var ORM
     */
    protected $orm;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $modelName;

    /**
     * Instruction constructor.
     *
     * @param ORM    $orm
     * @param string $modelName
     */
    public function __construct(ORM $orm, $modelName)
    {
        parent::__construct($orm->database(), $orm->database()->connectionName());

        $alias = $modelName;
        if (is_array($modelName))
        {
            $alias     = key($modelName);
            $modelName = current($modelName);
        }

        $this->orm   = $orm;
        $this->class = $orm->mapClass($modelName);
        $this->table = $orm->mapTable($modelName);

        $this->modelName = $modelName;

        $this->from([$alias => $this->table]);
    }

    /**
     * @return int
     */
    public function count()
    {
        $self = clone $this;
        $self->setSelect(['count' => $this->database->raw('COUNT(1)')]);

        $data = $self->findOne(false);

        return $data['count'];
    }

    /**
     * @param bool $asObject
     *
     * @return array|Entity[]
     */
    public function find($asObject = true)
    {
        if ($asObject)
        {
            $objects = $this
                ->database
                ->queryInstruction($this)
                ->fetchAll(
                    Connection::FETCH_CLASS,
                    $this->class,
                    [$this->orm, false, $this->table]
                );

            /**
             * @var Entity[] $objects
             */
            foreach ($objects as $object)
            {
                $object();
                $object->setModelName($this->modelName);
            }

            return $objects;
        }

        return parent::find();
    }

    /**
     * @param bool $asObject
     *
     * @return array|Entity
     */
    public function findOne($asObject = true)
    {
        if ($asObject)
        {
            $self = clone $this;
            $self->limit(1);

            $sth = $self
                ->database
                ->queryInstruction($self);

            /**
             * @var Entity $object
             */
            $object = $sth->fetchObject(
                $this->class,
                [$this->orm, false, $this->table]
            );

            if (!$object)
            {
                return null;
            }

            $object();
            $object->setModelName($this->modelName);

            $sth->closeCursor();

            return $object;
        }

        return parent::findOne();
    }

}