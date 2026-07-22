<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\HelperServiceProvider;
use App\Providers\SidebarServiceProvider;
use Yajra\DataTables\DataTablesServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    HelperServiceProvider::class,
    SidebarServiceProvider::class,
    DataTablesServiceProvider::class,
];
