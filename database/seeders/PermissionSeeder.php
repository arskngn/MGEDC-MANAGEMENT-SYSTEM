<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Remove legacy modules and permissions that are no longer part of the system
        Permission::whereIn('module', ['Warehouse', 'Transfer'])->delete();

        $permissions = [
            'Admin' => [
                'Dashboard',
            ],
            'Roles' => [
                'Roles Index',
                'Roles Add',
                'Roles Edit',
                'Roles Save',
            ],
            'Staff' => [
                'All Staffs',
                'Save Staff',
                'Staff Status',
            ],
            'Product' => [
                'All Product',
                'Add Product',
                'Product Store',
                'Product Edit',
                'Product Import',
                'Product Searching',
                'Download Product PDF',
                'Download Product CSV',
            ],
            'Category' => [
                'All Categorys',
                'Store Category',
                'Delete Category',
                'Import Category',
            ],
            'Brand' => [
                'All Brands',
                'Store Brand',
                'Delete Brand',
                'Import Brand',
            ],
            'Unit' => [
                'All Unit',
                'Store Unit',
                'Delete Unit',
                'Import Unit',
            ],
            'Purchase' => [
                'All Purchases',
                'New Purchase',
                'Edit Purchase',
                'Purchase Update',
                'Download Purchase PDF',
                'Download Purchase CSV',
                'Download Purchase Invoice PDF',
                'Store Supplier Payment',
            ],
            'PurchaseReturn' => [
                'All Purchase Return',
                'Store Purchase Return',
                'Edit Purchase Return',
                'Update Purchase Return',
                'Download Purchase Return PDF',
                'Download Purchase Return CSV',
                'Download Purchase Return Invoice PDF',
                'Store Supplier Payment Receive',
            ],
            'Sale' => [
                'All Sales',
                'Create Sale',
                'Store Sale',
                'Edit Sale',
                'Update Sale',
                'Confirm Sale',
                'Reject Sale',
                'Cancel Sale',
                'Update Sale Delivery Status',
                'Download Sales PDF',
                'Download Sales CSV',
                'Download Sale Invoice PDF',
                'Store Customer Payment',
            ],
            'SaleReturn' => [
                'All Sales Return',
                'Store Sale Return',
                'Edit Sale Return',
                'Update Sale Return',
                'Confirm Sale Return',
                'Reject Sale Return',
                'Download Sale Return PDF',
                'Download Sale Return CSV',
                'Download Sale Return Invoice PDF',
            ],
            'Customer' => [
                'All Customers',
                'Store Customer',
                'Import Customers',
                'Download Customer PDF',
                'Download Customer CSV',
            ],
            'Supplier' => [
                'All Suppliers',
                'Store Supplier',
                'Import Suppliers',
                'Download Supplier PDF',
                'Download Supplier CSV',
            ],
            'CustomerPayment' => [
                'Customer Payment Report',
                'All Customer Payments',
                'Clear Payment Of Customer',
                'Download Customer Payment PDF',
                'Download Customer Payment CSV',
            ],
            'SupplierPayment' => [
                'Supplier Payment Report',
                'Supplier Payment Index',
                'Supplier Payment Clear',
                'Download Supplier Payment PDF',
                'Download Supplier Payment CSV',
            ],
            'Adjustment' => [
                'All Adjustments',
                'Create Adjustment',
                'Store Adjustment',
                'Edit Adjustment',
                'Update Adjustment',
                'Delete Adjustment',
                'Adjustment Product Searching',
                'Download Adjustment PDF',
                'Download Adjustment CSV',
            ],
            'ExpenseType' => [
                'All Expense Types',
                'Store Expense Type',
                'Delete Expense Type',
                'Import Expense Types',
            ],
            'Expense' => [
                'All Expenses',
                'Store Expense',
                'Delete Expense',
                'Import Expenses',
                'Download Expense PDF',
                'Download Expense CSV',
            ],
            'StockReport' => [
                'Stock Report',
            ],
            'DataEntryReport' => [
                'Purchase Data Entry Report',
                'Purchase Return Data Entry Report',
                'Sale Data Entry Report',
                'Sale Return Data Entry Report',
                'Product Data Entry Report',
                'Customer Data Entry Report',
                'Supplier Data Entry Report',
                'Customer Payment Data Entry Report',
                'Supplier Payment Data Entry Report',
                'Report Data Entry Report Adjustment',
                'Expense Data Entry Report',
            ],
            'GeneralSetting' => [
                'Setting Index',
                'Update Setting',
                'Setting Logo Icon',
                'Store Setting Logo Icon',
                'Setting System Configuration',
                'Store Setting System Configuration',
            ],
            'Notification' => [
                'Email Notification Setting',
                'Notification Setting Email Update',
                'Notification Setting Email Test',
                'Notification Setting SMS Test',
                'Setting Notification Template Update',
            ],
            'System' => [
                'System Info',
                'System Server Info',
                'System Optimize',
            ],
        ];

        // Gather all valid permission names
        $validPermissionNames = [];
        foreach ($permissions as $module => $perms) {
            foreach ($perms as $perm) {
                $validPermissionNames[] = $perm;
                Permission::updateOrCreate(
                    ['name' => $perm],
                    ['module' => $module]
                );
            }
        }

        // Clean up orphan permissions from database that are no longer valid
        Permission::whereNotIn('name', $validPermissionNames)->delete();
    }
}
