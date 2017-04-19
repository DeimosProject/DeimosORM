<?php

include_once dirname(__DIR__) . '/vendor/autoload.php';

class User extends \Deimos\ORM\Entity
{

}

class Role extends \Deimos\ORM\Entity
{

}

class Asd extends \Deimos\ORM\Entity
{
    protected $table = 'asd';
}

$builder = new \Deimos\Builder\Builder();
$helper  = new \Deimos\Helper\Helper($builder);
$slice   = new \Deimos\Slice\Slice($helper, [
    'adapter'  => 'mysql',
    //    'host'     => 'localhost', // optional
    //    'port'     => 3306, // optional
    'database' => 'test',
    'username' => 'root',
    'password' => 'root'
]);

$database = new \Deimos\Database\Database($slice);

$orm = new \Deimos\ORM\ORM($helper, $database);

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
$orm->register('asd', Asd::class);

$orm->register('user', User::class, [

    // array key === callback name
    'roles'  => [
        'type'  => 'manyToMany',
//        'table' => 'rolesUsers',  // optional, default usersRoles
//
                'left'   => 'role',
                'leftId' => 'roleId',    // optional
//        //
//        //        'right'   => 'user',      // optional [callback name for roles]
                'rightId' => 'userId',   // optional
    ],

    // array key === callback name
    'images' => [
        'type'  => 'oneToMany',

        'left' => 'image',

//        'right' => 'user',      // optional [callback name for image]
    ],

    'asd' => [
        'type' => 'oneToMany',
        'left' => 'asd'
    ]

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

echo $user;
die;

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