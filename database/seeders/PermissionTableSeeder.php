<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',
            'lottery-list',
            'lottery-create',
            'lottery-edit',
            'lottery-delete',
            'payment-check',
            'payment-create',
            'payment-edit',
            'payment-delete',
            'payment-approve',
            'config-create',
            'config-edit',
            'config-delete',
            'dashboard-admin',
            'dashboard-finance',
            'dashboard-application',
            'customer-list',
            'customer-create',
            'customer-edit',
            'customer-delete',
            'customer-dashboard',
            'system-audit',
            'admin-audit',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}
