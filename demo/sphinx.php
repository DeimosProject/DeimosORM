<?php

include_once __DIR__ . '/../bootstrap.php';

$builder = new \Deimos\ORM\Builder();

$builder->setConnection('sphinx');

\Deimos\ORM\Config::setStorage([
    [
        'type'     => $builder::ONE2MANY,
        'owner'    => Brand::class,
        'items'    => Event::class,
        'itemsKey' => 'brand_id'
    ]
]);

//$people = $builder->queryEntity(Person::class)
//    ->limit(100)
//    ->find();
//
//var_dump($people);

$eventQuery = $builder->queryEntity(Event::class)
    ->sphinxMatch('hello')
    ->limit(1000)
    ->sphinxOption();

var_dump($event = $eventQuery->findOne());

$builder->setConnection();

var_dump($event->relation(Brand::class, $builder::ONE2MANY)->findOne());