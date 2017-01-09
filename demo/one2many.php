<?php

include_once __DIR__ . '/../bootstrap.php';

$builder = new \Deimos\ORM\Builder();

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
    ->where('txt', 'like', '%hello%')
    ->limit(1000);

var_dump($event = $eventQuery->findOne());
var_dump($event->oneToMany(Brand::class)->findOne());