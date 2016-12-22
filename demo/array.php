<?php

include_once __DIR__ . '/../bootstrap.php';

$builder = new \Deimos\ORM\Builder();

$query = $builder->queryEntity(Event::class);
$query->orderBy('id', 'DESC');
$event = $query->findOne();

var_dump($event->asArray());