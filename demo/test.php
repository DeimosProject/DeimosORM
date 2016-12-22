<?php

include_once __DIR__ . '/../bootstrap.php';

$builder = new \Deimos\ORM\Builder();

$people = $builder->queryEntity(Person::class)
    ->limit(100)
    ->find();

var_dump($people);