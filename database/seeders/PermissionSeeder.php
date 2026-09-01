<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Admin;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = config('permissions-modules.modules');

        if (!Permission::where('name', 'dashboard allow')->where('guard_name', 'admin')->exists()) {
            Permission::create(['name' => 'dashboard allow', 'guard_name' => 'admin']);
        }
        if (!Permission::where('name', 'setting allow')->where('guard_name', 'admin')->exists()) {
            Permission::create(['name' => 'setting allow', 'guard_name' => 'admin']);
        }
        if (!Permission::where('name', 'vendors kyc')->where('guard_name', 'admin')->exists()) {
            Permission::create(['name' => 'vendors kyc', 'guard_name' => 'admin']);
        }

        foreach ($modules as $row) {
            if (!Permission::where('name', $row . " add")->where('guard_name', 'admin')->exists()) {
                Permission::create(['name' => $row . " add", 'guard_name' => 'admin']);
                Permission::create(['name' => $row . " edit", 'guard_name' => 'admin']);
                Permission::create(['name' => $row . " delete", 'guard_name' => 'admin']);
                Permission::create(['name' => $row . " view", 'guard_name' => 'admin']);
            }
        }

        $admins = Admin::where('email', 'backupilogicx@gmail.com')->get();
        $all_permissions = Permission::where('guard_name', 'admin')->get();

        // 1. Admin Role
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'admin']);
        $adminRole->syncPermissions($all_permissions);

        // 2. Manager Role
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'admin']);
        $managerRole->syncPermissions([
            'dashboard allow',
            'manager view', 'manager add', 'manager edit',
            'vendors view', 'vendors add', 'vendors edit', 'vendors kyc',
            'vehicles view', 'vehicles add', 'vehicles edit', 'vehicles delete',
            'drivers view', 'drivers add', 'drivers edit', 'drivers delete'
        ]);

        // 3. Staff Role
        $staffRole = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'admin']);
        $staffRole->syncPermissions([
            'dashboard allow',
            'vendors view', 'vendors add',
            'vehicles view', 'vehicles add', 'vehicles edit'
        ]);

        // 4. Driver Role
        Role::firstOrCreate(['name' => 'driver', 'guard_name' => 'admin']);
        Role::firstOrCreate(['name' => 'driver', 'guard_name' => 'driver']);

        // 5. Customer Role
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'admin']);

        // Assign Admin role to the default admin user
        foreach ($admins as $adminUser) {
            $adminUser->assignRole('admin');
        }
    }
}
