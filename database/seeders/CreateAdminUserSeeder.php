<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateAdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'waiyanmaing.dev@gmail.com',
            'password' => bcrypt('Dev@1234!@#$')
        ]);

        // Create Admin role
        $role = Role::create(['name' => 'admin']);

        // Get all permissions and assign to admin role
        $permissions = Permission::pluck('id', 'id')->all();
        $role->syncPermissions($permissions);

        // Assign admin role to user
        $user->assignRole([$role->id]);

        echo "✓ Admin user created successfully\n";
        echo "  Email: waiyanmaing.dev@gmail.com\n";
        echo "  Password: Dev@1234!@#$\n";
    }
}
