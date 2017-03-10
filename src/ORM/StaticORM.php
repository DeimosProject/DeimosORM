<?php

namespace Deimos\ORM;

class StaticORM
{

    /**
     * @var ORM
     */
    protected static $orm;

    /**
     * @param ORM $orm
     */
    public static function setORM(ORM $orm)
    {
        static::$orm = $orm;
    }

    /**
     * @return ORM
     */
    public static function getORM()
    {
        return static::$orm;
    }

}