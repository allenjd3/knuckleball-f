<?php

use App\Console\Commands\TakeUserSnapshots;
use Illuminate\Support\Facades\Schedule;

Schedule::command('temp-directory:clean')->hourly();

Schedule::command('notify:pending-approvals')->weeklyOn(2, '1:00');

Schedule::command('snapshots:take')->yearlyOn(12, 31, '23:59');
