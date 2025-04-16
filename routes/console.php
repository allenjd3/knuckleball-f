<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('notify:pending-approvals')->weeklyOn(2, '1:00');
