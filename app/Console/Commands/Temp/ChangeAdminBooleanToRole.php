<?php

namespace App\Console\Commands\Temp;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Console\Command;

class ChangeAdminBooleanToRole extends Command
{
    protected $signature = 'operation:change-admin-boolean-to-role';

    protected $description = 'Change admin boolean to role';

    public function handle()
    {
        User::where('super_admin', true)
            ->update([
                'role' => Role::ADMIN,
            ]);
    }
}
