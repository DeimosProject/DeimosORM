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

$orm = new \Deimos\ORM\ORM($database);

//$orm->register('user', User::class/*, [
//    'role' => [
//        'type'  => 'manyToMany',
//
//
//
//        'table' => 'usersRoles',
//
//        'left'  => 'user',
//        'right' => 'role',
//    ]
//]*/);

$user = $orm->repository('user')
    ->orderBy('id', 'DESC')
    ->findOne();

var_dump($user);

//$orm->create('user')->save([
//    'first_name' => 'test',
//    'last_name' => 'test',
//]);

var_dump($orm->repository('user')->count());