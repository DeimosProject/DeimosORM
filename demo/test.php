<?php

include_once __DIR__ . '/../bootstrap.php';

$builder = new \Deimos\ORM\Builder();

$builder->setConnection('sphinx');

//$people = $builder->queryEntity(Person::class)
//    ->limit(100)
//    ->find();
//
//var_dump($people);

$events = $builder->queryEntity(Event::class)
    ->where($builder->sqlExression('MATCH(?)', ['hello']))
    ->limit(1000)
    ->find();

var_dump($events);