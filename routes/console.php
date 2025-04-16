<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('notify:pending-approvals')->weeklyOn(2, '1:00');
