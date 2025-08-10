<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Built-in the system users
        event(new Registered(($user = User::create(
            [
                'name' => 'Super Admin',
                'email' => 'super-admin@example.com',
                'password' => Hash::make('password'),
                'is_tenant' => true,
                'is_system' => true,
                'system_id' => 0,
            ]

        ))));

        // Assign the super-admin role to the user
        $user->assignRole('super-admin-for-system');
    }
}
