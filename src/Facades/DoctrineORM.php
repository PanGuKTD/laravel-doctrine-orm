<?php

namespace PanGuKTD\LaravelDoctrineORM\Facades;

use Illuminate\Support\Facades\Facade;

class DoctrineORM extends Facade {
    
    public static function getFacadeAccessor()
    {
        return 'doctrine_orm';
    }
}

