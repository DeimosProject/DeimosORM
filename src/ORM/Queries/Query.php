<?php

namespace Deimos\ORM\Queries;

use Deimos\Database\Connection;
use Deimos\Database\Database;
use Deimos\ORM\Entity;

class Query extends \Deimos\Database\Queries\Query
{

    /**
     * @var string $class
     */
    protected $class;

    /**
     * @var string $table
     */
    protected $table;

    /**
     * Instruction constructor.
     *
     * @param Database $database
     * @param string   $class
     * @param string   $table
     */
    public function __construct(Database $database, $class, $table)
    {
        parent::__construct($database);
        $this->class = $class;
        $this->table = $table;

        $this->from($this->table);
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
                    [$this->database, false, $this->table]
                );

            foreach ($objects as $object)
            {
                $object();
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

            $object = $sth->fetchObject(
                $this->class,
                [$this->database, false, $this->table]
            );

            $object();
            $sth->closeCursor();

            return $object;
        }

        return parent::findOne();
    }

}