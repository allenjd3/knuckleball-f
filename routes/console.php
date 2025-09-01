<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('temp-directory:clean')->hourly();

Schedule::command('notify:pending-approvals')->weeklyOn(2, '1:00');
