<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class AccountOfficerPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            ['category' => 'Account Officer Permissions', 'name' => 'officer-transfer-manage'],
            ['category' => 'Account Officer Permissions', 'name' => 'officer-user-manage'],
            ['category' => 'Account Officer Permissions', 'name' => 'officer-security-manage'],
            ['category' => 'Account Officer Permissions', 'name' => 'officer-deposit-manage'],
            ['category' => 'Account Officer Permissions', 'name' => 'officer-card-manage'],
            ['category' => 'Account Officer Permissions', 'name' => 'officer-balance-manage'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name'], 'guard_name' => 'admin'],
                ['category' => $permission['category']]
            );
        }
    }
}
