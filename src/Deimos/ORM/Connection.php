<?php

namespace Deimos\ORM;

class Connection extends \PDO
{

    /**
     * @var array
     */
    protected static $connections = [];

    /**
     * @var array
     */
    protected static $config = [];

    /**
     * @param array $config
     */
    public static function setConfig($config)
    {
        static::$config = $config;
    }

    /**
     * @param string $name
     *
     * @return static
     */
    public static function get($name)
    {
        if (!isset(static::$connections[$name]))
        {
            $config = static::$config[$name];

            static::$connections[$name] = static::sharedInstance(
                $config['dsn'],
                $config['username'],
                $config['password'],
                isset($config['options']) ? $config['options'] : []
            );
        }

        return static::$connections[$name];
    }

    /**
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param array  $options
     *
     * @return static
     */
    public static function sharedInstance($dsn, $username, $password, array $options = [])
    {
        $options += [
            static::ATTR_DEFAULT_FETCH_MODE       => static::FETCH_ASSOC,
            static::ATTR_EMULATE_PREPARES         => true,
            static::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            static::ATTR_PERSISTENT               => false,
            static::MYSQL_ATTR_INIT_COMMAND       => 'SET NAMES utf8mb4'
        ];

        return new static($dsn, $username, $password, $options);
    }

}