<?php

include_once dirname(__DIR__) . '/vendor/autoload.php';

class User extends \Deimos\ORM\Entity
{

}

$builder = new \Deimos\Builder\Builder();

$configObject = new \Deimos\Config\ConfigObject($builder, [
    'adapter'  => 'mysql',
    //    'host'     => 'localhost', // optional
    //    'port'     => 3306, // optional
    'database' => 'test',
    'username' => 'root',
    'password' => ''
]);

$database = new \Deimos\Database\Database($configObject);

$orm = new \Deimos\ORM\ORM($builder, $database);

//$orm->setConfig([
//
//    'user' => [
//        'class'     => \Deimos\ORM\Entity::class,
//        'relations' => [
//
//            // array key === callback name
//            'roles'  => [
//                'type'  => 'manyToMany',
//                'table' => 'usersRoles',  // optional, default usersRoles
//
//                'left'   => 'role',
//                'leftId' => 'roleId',    // optional
//
//                'right'   => 'user',      // optional [callback name for roles]
//                'rightId' => 'user_id',   // optional
//            ],
//
//            // array key === callback name
//            'images' => [
//                'type'  => 'oneToMany',
//                'table' => 'imagesUsers',// optional, default usersImages
//
//                'left' => 'image',
//
//                'right'   => 'user',      // optional [callback name for image]
//                'rightId' => 'user_id',   // optional
//            ],
//
//        ]
//    ]
//
//]);

$orm->register('user', \Deimos\ORM\Entity::class, [

    // array key === callback name
    'roles'  => [
        'type'  => 'manyToMany',
//        'table' => 'usersRoles',  // optional, default usersRoles
//
//        'left'   => 'role',
//        'leftId' => 'roleId',    // optional
//
//        'right'   => 'user',      // optional [callback name for roles]
//        'rightId' => 'user_id',   // optional
    ],

    // array key === callback name
    'images' => [
        'type'  => 'oneToMany',
        'table' => 'imagesUsers',// optional, default usersImages

        'left' => 'image',

        'right'   => 'user',      // optional [callback name for image]
        'rightId' => 'user_id',   // optional
    ],

]);

$user = $orm->repository('user')
    ->orderBy('id', 'DESC')
    ->findOne();

// todo
//$user->fetch('roles', ['images1' =>  'images']);

//$user->roles() // manyToMany, oneToMany
//$role->user()

var_dump($user);

//(new User($orm))->save([
//    'first_name' => 'test',
//    'last_name' => 'test',
//]);

//$orm->create('user')->save([
//    'first_name' => 'test',
//    'last_name' => 'test',
//]);

var_dump($orm->repository('user')->count());