<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('temp-directory:clean')->hourly();
Schedule::command('shops:post-spotlights')->monthlyOn(1, '09:00');

Schedule::command('notify:pending-approvals')->weeklyOn(2, '1:00');
Schedule::command('featured:expire')->dailyAt('02:00');

Schedule::command('snapshots:take')->yearlyOn(12, 31, '23:59');

Schedule::command('notify:expiring-payment-methods')->monthlyOn(1, '09:00');
