<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'admin']);

        // Rename old 'staff' to 'encoder' if it exists
        $staff = Role::where('name', 'staff')->first();
        if ($staff) {
            $staff->update(['name' => 'encoder']);
        } else {
            Role::firstOrCreate(['name' => 'encoder']);
        }

        Role::firstOrCreate(['name' => 'salesperson']);
    }
}
