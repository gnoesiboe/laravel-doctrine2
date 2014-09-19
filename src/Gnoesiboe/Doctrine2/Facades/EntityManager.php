<?php

namespace Gnoesiboe\Doctrine2\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Doctrine2
 */
class EntityManager extends Facade
{

    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'doctrine2.entityManager';
    }
}
