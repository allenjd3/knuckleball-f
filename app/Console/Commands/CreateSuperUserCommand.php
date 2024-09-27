<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateSuperUserCommand extends Command
{
    protected $signature = 'create:super-user';

    protected $description = 'Command description';

    public function handle(): void
    {
        $name = $this->ask('Enter name');
        $email = $this->ask('Enter email');
        $password = $this->secret('Enter password');
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $user->super_admin = true;
        $user->published_at = now();

        if ($user->save()) {
            $this->info('Successfully created user- ' . $user->id);
        } else {
            $this->error('Something went wrong');
        }
    }
}
