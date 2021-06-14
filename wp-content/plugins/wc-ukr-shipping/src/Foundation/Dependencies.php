<?php

namespace kirillbdev\WCUkrShipping\Foundation;

use kirillbdev\WCUkrShipping\Modules\Core\Activator;
use kirillbdev\WCUSCore\DB\Migrator;

if ( ! defined('ABSPATH')) {
    exit;
}

final class Dependencies
{
    public static function all()
    {
        return [
            // Modules
            Activator::class => function ($container) {
                return new Activator($container->make(Migrator::class));
            }
        ];
    }
}