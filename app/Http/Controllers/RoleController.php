<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RoleController extends Controller
{
    public function index()
    {
        $perPage = GeneralSetting::first()->records_per_page ?? 10;
        $roles = Role::paginate($perPage);

        return view('staff.roles.index', compact('roles'));
    }

    public function create()
    {
        $modules = $this->permissionsGroupedForRoleForm();

        return view('staff.roles.create', compact('modules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->permissions()->attach($request->permissions);
        }

        return redirect()->route('staff.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        if (strtolower($role->name) === 'admin') {
            return redirect()->route('staff.roles.index')->with('error', 'The admin role is protected and cannot be edited.');
        }

        $modules = $this->permissionsGroupedForRoleForm();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('staff.roles.edit', compact('role', 'modules', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        if (strtolower($role->name) === 'admin') {
            return redirect()->route('staff.roles.index')->with('error', 'The admin role is protected and cannot be modified.');
        }

        $request->validate([
            'name' => 'required|string|unique:roles,name,'.$role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update(['name' => $request->name]);

        $role->permissions()->sync($request->permissions ?? []);

        // Invalidate permissions cache for all users assigned to this role
        \App\Services\PermissionCacheService::invalidateRolePermissions($role->id);

        return redirect()->route('staff.roles.index')->with('success', 'Role updated successfully.');
    }

    /**
     * Group permissions by module in a stable order so role UI matches app areas (e.g. Product, Category, Brand, Unit together).
     *
     * @return Collection<string, Collection<int, Permission>>
     */
    private function permissionsGroupedForRoleForm(): Collection
    {
        $moduleOrder = [
            'Admin', 'Roles', 'Staff',
            'Product', 'Category', 'Brand', 'Unit',
            'Purchase', 'PurchaseReturn', 'Sale', 'SaleReturn',
            'Customer', 'Supplier',
            'CustomerPayment', 'SupplierPayment',
            'Adjustment',
            'ExpenseType', 'Expense',
            'StockReport', 'DataEntryReport',
            'GeneralSetting', 'Notification', 'System',
        ];

        $grouped = Permission::query()
            ->whereNotIn('module', ['Warehouse', 'Transfer'])
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        $ordered = collect();
        foreach ($moduleOrder as $module) {
            if ($grouped->has($module)) {
                $ordered->put($module, $grouped->get($module));
            }
        }

        foreach ($grouped->keys()->diff($moduleOrder)->sort()->values() as $module) {
            $ordered->put($module, $grouped->get($module));
        }

        return $ordered;
    }
}
