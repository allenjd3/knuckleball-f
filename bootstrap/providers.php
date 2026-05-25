<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\CpPanelProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\JetstreamServiceProvider;

return [
    AppServiceProvider::class,
    CpPanelProvider::class,
    FortifyServiceProvider::class,
    JetstreamServiceProvider::class,
];
