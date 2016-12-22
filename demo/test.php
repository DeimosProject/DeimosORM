<?php

include_once __DIR__ . '/../bootstrap.php';

$builder = new \Deimos\ORM\Builder();

$builder->setConnection('sphinx');

//$people = $builder->queryEntity(Person::class)
//    ->limit(100)
//    ->find();
//
//var_dump($people);

$event = $builder->queryEntity(Event::class)
    ->sphinxMatch('hello')
    ->limit(1000)
    ->find();

var_dump($event);