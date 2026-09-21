<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => Role::ROLE_ADMIN,
                'description' => 'Full control over system, POS, catalog, users, and store settings.',
            ],
            [
                'name' => 'Store Manager',
                'slug' => Role::ROLE_MANAGER,
                'description' => 'Manage products, stock levels, suppliers, and sales analytics.',
            ],
            [
                'name' => 'POS Cashier',
                'slug' => Role::ROLE_CASHIER,
                'description' => 'Operate POS cashier desk, process customer checkouts and cash shift drawer.',
            ],
            [
                'name' => 'Customer',
                'slug' => Role::ROLE_USER,
                'description' => 'Registered online shoppers, order history, and account profile.',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }
    }
}
