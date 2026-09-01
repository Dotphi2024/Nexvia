<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Admin;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $admin = Admin::where('email', 'backupilogicx@gmail.com')->first();
        if(!$admin){
            $admin = new Admin();
            $admin->name = 'Admin';
            $admin->email = 'backupilogicx@gmail.com';
            $admin->phone='1122334455';
            $admin->password = Hash::make('backupilogicx@11');
            $admin->save();
        }

        $this->call(PermissionSeeder::class);
    }
}
