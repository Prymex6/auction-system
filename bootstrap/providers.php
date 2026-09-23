<?php

use App\Providers\AppServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\ObserverServiceProvider;

return [
    AppServiceProvider::class,
    ObserverServiceProvider::class,
    EventServiceProvider::class,
];
