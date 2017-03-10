<?php

include_once dirname(__DIR__) . '/vendor/autoload.php';

class User extends \Deimos\ORM\Entity
{

}

class Role extends \Deimos\ORM\Entity
{

}

$builder = new \Deimos\Builder\Builder();

$configObject = new \Deimos\Config\ConfigObject($builder, [
    'adapter'  => 'mysql',
    //    'host'     => 'localhost', // optional
    //    'port'     => 3306, // optional
    'database' => 'test',
    'username' => 'root',
    'password' => 'root'
]);

$database = new \Deimos\Database\Database($configObject);

$orm = new \Deimos\ORM\ORM($builder, $database);

$orm->register('role', Role::class);

$orm->register('user', User::class, [

    // array key === callback name
    'roles'  => [
        'type'    => 'manyToMany',
        //        'table' => 'rolesUsers',  // optional, default usersRoles
        //
        'left'    => 'role',
        'leftId'  => 'roleKey',    // optional
        //        //
        //        //        'right'   => 'user',      // optional [callback name for roles]
        'rightId' => 'user_id',   // optional
    ],

    // array key === callback name
    'images' => [
        'type' => 'oneToMany',

        'left'    => 'image',

        //        'right' => 'user',      // optional [callback name for image]
        'rightId' => 'user_key',   // optional
    ],

]);

\Deimos\ORM\StaticORM::setORM($orm);

var_dump(User::findById(1));