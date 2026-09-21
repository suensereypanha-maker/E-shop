<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminRole = Role::where('slug', Role::ROLE_ADMIN)->first();
        $managerRole = Role::where('slug', Role::ROLE_MANAGER)->first();
        $cashierRole = Role::where('slug', Role::ROLE_CASHIER)->first();
        $userRole = Role::where('slug', Role::ROLE_USER)->first();

        $users = [
            [
                'name' => 'System Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role_id' => $adminRole?->id,
            ],
            [
                'name' => 'Store Manager',
                'email' => 'manager@example.com',
                'password' => Hash::make('password'),
                'role_id' => $managerRole?->id,
            ],
            [
                'name' => 'POS Cashier Sokha',
                'email' => 'cashier@example.com',
                'password' => Hash::make('password'),
                'role_id' => $cashierRole?->id,
            ],
            [
                'name' => 'Customer Panha',
                'email' => 'user@example.com',
                'password' => Hash::make('password'),
                'role_id' => $userRole?->id,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
