<?php

return [
    'name' => 'array', //apc,xcache,memcache,memcached,redis,xcache,array
    
    'memcache' => [
        ['host' => 'localhost', 'port' => 11211, 'weight' => 1, 'persistent' => false]
    ],
    'memcached' => [
        ['host' => 'localhost', 'port' => 11211, 'weight' => 1]
    ],
    'mongo' => [
        'host' => '127.0.0.1', 'port' => '27017', 'options' => []
    ],
    'redis' => [
        'host' => '127.0.0.1', 'port' => '27017'
    ]
];

