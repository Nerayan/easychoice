<?php

namespace kirillbdev\WCUkrShipping\Modules;

use kirillbdev\WCUkrShipping\Contracts\ModuleInterface;
use kirillbdev\WCUkrShipping\DB\Migrations\CreateAreasTable;
use kirillbdev\WCUkrShipping\DB\Migrations\CreateCitiesTable;
use kirillbdev\WCUkrShipping\DB\Migrations\CreateIndexes;
use kirillbdev\WCUkrShipping\DB\Migrations\CreateWarehousesTable;
use kirillbdev\WCUkrShipping\DB\Migrator;

if ( ! defined('ABSPATH')) {
    exit;
}

class Activator implements ModuleInterface
{
    public function init()
    {
        add_action('plugins_loaded', [$this, 'activate']);
        register_activation_hook(WC_UKR_SHIPPING_PLUGIN_ENTRY, [$this, 'activate']);
    }

    public function activate()
    {
        $migrator = new Migrator();

        $migrator->addMigration(new CreateAreasTable());
        $migrator->addMigration(new CreateCitiesTable());
        $migrator->addMigration(new CreateWarehousesTable());
        $migrator->addMigration(new CreateIndexes());
        $migrator->run();
    }
}