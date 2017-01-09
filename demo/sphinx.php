<?php

include_once __DIR__ . '/../bootstrap.php';

$builder = new \Deimos\ORM\Builder();

$builder->setConnection('sphinx');

$builder->config()->setStorage([
    [
        'type'     => \Deimos\ORM\Constant\Relation::ONE2MANY,
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

var_dump($event->oneToMany(Brand::class)->findOne());