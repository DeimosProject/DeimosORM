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
    ->findOne();

$event->title = 'I’m like “Hey, what’s up, hello!” ?';

$builder->setConnection();

$event->save();

var_dump($event);