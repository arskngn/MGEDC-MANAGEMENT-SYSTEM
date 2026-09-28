<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure roles exist in case UserSeeder is run independently
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        $encoderRole = Role::where('name', 'staff')->first();
        if ($encoderRole) {
            $encoderRole->update(['name' => 'encoder']);
        } else {
            $encoderRole = Role::firstOrCreate(['name' => 'encoder']);
        }

        $salespersonRole = Role::firstOrCreate(['name' => 'salesperson']);

        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'phone' => '09123456789',
                'password' => Hash::make('password'),
                'status' => 1,
                'role_id' => $adminRole->id,
            ]
        );
        if ($admin->role_id !== $adminRole->id) {
            $admin->update(['role_id' => $adminRole->id]);
        }

        // Give admin all permissions
        $adminRole->permissions()->sync(Permission::all());

        // Create Encoder User
        $encoder = User::firstOrCreate(
            ['email' => 'encoder@example.com'],
            [
                'name' => 'Encoder User',
                'phone' => '09987654321',
                'password' => Hash::make('password'),
                'status' => 1,
                'role_id' => $encoderRole->id,
            ]
        );
        if ($encoder->role_id !== $encoderRole->id) {
            $encoder->update(['role_id' => $encoderRole->id]);
        }

        // Migrate old staff@example.com to encoder role if it exists
        $oldStaff = User::where('email', 'staff@example.com')->first();
        if ($oldStaff) {
            $oldStaff->update(['role_id' => $encoderRole->id]);
        }

        $encoderPermissions = [
            'Dashboard',
            'All Product', 'Add Product', 'Product Edit', 'Product Store', 'Product Alert', 'Product Import', 'Product Searching', 'Download Product PDF', 'Download Product CSV',
            'All Categorys', 'Delete Category', 'Store Category', 'Import Category',
            'All Brands', 'Delete Brand', 'Store Brand', 'Import Brand',
            'All Unit', 'Delete Unit', 'Store Unit', 'Import Unit',
            'All Purchases', 'New Purchase', 'Edit Purchase', 'Store Purchase', 'Download Purchase PDF', 'Purchase Update', 'Product Searching', 'Check Purchase Invoice', 'Download Purchase CSV', 'Download Purchase Invoice PDF', 'Store Supplier Payment',
            'Item Of Purchase Return', 'All Purchase Return', 'Store Purchase Return', 'Edit Purchase Return', 'Update Purchase Return', 'Download Purchase Return PDF', 'Purchase Return Check Invoice', 'Download Purchase Return CSV', 'Download Purchase Return Invoice PDF',
            'All Sales', 'Create Sale', 'Store Sale', 'Edit Sale', 'Update Sale', 'Download Sales PDF', 'Sale Search Product', 'Customer Searching', 'Last Sale Invoice', 'Download Sales CSV', 'Download Sale Invoice PDF', 'Store Customer Payment', 'Confirm Sale', 'Reject Sale', 'Cancel Sale',
            'All Sales Return', 'Item Of Sale Return', 'Store Sale Return', 'Edit Sale Return', 'Update Sale Return', 'Confirm Sale Return', 'Reject Sale Return', 'Download Sale Return PDF', 'Sale Return Search Product', 'Sale Return Search Customer', 'Download Sale Return CSV', 'Download Sale Return Invoice PDF',
            'All Adjustments', 'Create Adjustment', 'Store Adjustment', 'Download Adjustment Details PDF', 'Edit Adjustment', 'Update Adjustment', 'Adjustment Product Searching', 'Download Adjustment PDF', 'Download Adjustment CSV',
            'All Suppliers', 'Store Supplier', 'Import Suppliers', 'Download Supplier PDF', 'Download Supplier CSV',
            'All Customers', 'Store Customer', 'Import Customers', 'Download Customer PDF', 'Download Customer CSV',
            'All Transfers', 'Create Transfer', 'Edit Transfer', 'Store Transfer', 'Download Transfer PDF', 'Update Transfer', 'Transfer Product Search', 'Download Transfer CSV', 'Download Transfer Details PDF',
            'All Expenses', 'Store Expense', 'Import Expenses', 'Download Expense PDF', 'Download Expense CSV',
            'Supplier Payment Report', 'Customer Payment Report',
            'Supplier Payment Index', 'Supplier Payment Clear', 'Store Supplier Payment', 'Store Supplier Payment Receive', 'Download Supplier Payment PDF', 'Download Supplier Payment CSV',
            'All Customer Payments', 'Clear Payment Of Customer', 'Store Customer Payment', 'Store Payable Payment Of Customer', 'Download Customer Payment PDF', 'Download Customer Payment CSV',
            'Stock Report', 'Download Stock Report PDF', 'Download Stock Report CSV',
            'Product Data Entry Report', 'Customer Data Entry Report', 'Supplier Data Entry Report', 'Purchase Data Entry Report',
            'Purchase Return Data Entry Report', 'Sale Data Entry Report', 'Sale Return Data Entry Report', 'Report Data Entry Report Adjustment',
            'Transfer Data Entry Report', 'Expense Data Entry Report', 'Supplier Payment Data Entry Report', 'Customer Payment Data Entry Report',
            'Setting Index', 'Update Setting',
        ];

        $permissionIds = Permission::whereIn('name', $encoderPermissions)->pluck('id');
        $encoderRole->permissions()->sync($permissionIds);

        // Create Salesperson User
        $salesperson = User::firstOrCreate(
            ['email' => 'salesperson@example.com'],
            [
                'name' => 'Sales Person',
                'phone' => '09171234567',
                'password' => Hash::make('password'),
                'status' => 1,
                'role_id' => $salespersonRole->id,
            ]
        );
        if ($salesperson->role_id !== $salespersonRole->id) {
            $salesperson->update(['role_id' => $salespersonRole->id]);
        }

        $salespersonPermissions = [
            'Dashboard',
            'All Product', 'Product Searching',
            'All Sales', 'Create Sale', 'Store Sale', 'Sale Search Product', 'Customer Searching', 'Last Sale Invoice', 'View Own Sales Only',
            'All Customers', 'Store Customer', 'Download Customer PDF', 'Download Customer CSV',
            'Setting Index', 'Update Setting',
        ];

        $permissionIds = Permission::whereIn('name', $salespersonPermissions)->pluck('id');
        $salespersonRole->permissions()->sync($permissionIds);
    }
}

