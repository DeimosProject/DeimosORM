<?php

include_once __DIR__ . '/../bootstrap.php';

$builder = new \Deimos\ORM\Builder();

$query = $builder->queryEntity(Event::class);
$query->orderBy('id', 'DESC');
$event = $query->findOne();

echo(json_encode($event, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));