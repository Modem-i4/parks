<?php

$providers = [
    App\Providers\AppServiceProvider::class,
];

if (env('APP_ENV') === 'local' && class_exists(App\Providers\TelescopeServiceProvider::class)) {
    $providers[] = App\Providers\TelescopeServiceProvider::class;
}

return $providers;