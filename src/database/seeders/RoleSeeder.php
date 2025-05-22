<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $superAdminRole = Role::create(['name' => 'super_admin']);
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);
        
        // Create permissions
        // You can add specific permissions based on your application needs
        $permissions = [
            'view_dashboard',
            'manage_users',
            'manage_roles',
            'manage_merchants',
            'manage_customers',
            'manage_invoices',
            'manage_payments',
            'manage_payment_methods',
        ];
        
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        
        // Assign all permissions to super_admin
        $superAdminRole->givePermissionTo(Permission::all());
        
        // Assign specific permissions to admin
        $adminRole->givePermissionTo([
            'view_dashboard',
            'manage_merchants',
            'manage_customers',
            'manage_invoices',
            'manage_payments',
        ]);
        
        // Assign limited permissions to user
        $userRole->givePermissionTo([
            'view_dashboard',
        ]);
    }
}
