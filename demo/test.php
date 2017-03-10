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

$orm->register('role', Role::class);

$orm->register('user', User::class, [

    // array key === callback name
    'roles'  => [
        'type'  => 'manyToMany',
//        'table' => 'rolesUsers',  // optional, default usersRoles
//
                'left'   => 'role',
                'leftId' => 'roleKey',    // optional
//        //
//        //        'right'   => 'user',      // optional [callback name for roles]
                'rightId' => 'user_id',   // optional
    ],

    // array key === callback name
    'images' => [
        'type'  => 'oneToMany',

        'left' => 'image',

//        'right' => 'user',      // optional [callback name for image]
                'rightId' => 'user_key',   // optional
    ],

]);

$user = $orm->repository('user')
//    ->orderBy('id', 'DESC')
    ->findOne();

if (!$user)
{
    $user = $orm->create('user', [
        'firstName' => 'Maxim',
        'lastName'  => 'Babichev',
    ]);
}

$roles = $user->roles();

var_dump( $roles->findOne()->users()->find() );

//$role->user()

//var_dump($user->roles()->find(false));
//die;

//(new User($orm))->save([
//    'first_name' => 'test',
//    'last_name' => 'test',
//]);

//$orm->create('user')->save([
//    'first_name' => 'test',
//    'last_name' => 'test',
//]);

//$orm->create('user', [
//    'first_name' => 'test',
//    'last_name' => 'test',
//]);

var_dump($orm->repository('user')->count());