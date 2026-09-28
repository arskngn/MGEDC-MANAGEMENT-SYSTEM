<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use App\Models\Role;
use App\Models\User;
use App\Events\StaffCreated;
use App\Services\AppearanceSettingService;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\StaffTarget;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function __construct(protected AppearanceSettingService $appearance) {}
    public function index(Request $request)
    {
        $query = User::with('role');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        if ($request->filled('role')) {
            $roleId = $request->input('role');
            $query->where('role_id', $roleId);
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'online') {
                $query->where('last_seen', '>', now()->subMinutes(5));
            } elseif ($status === 'offline') {
                $query->where(function ($q) {
                    $q->where('last_seen', '<=', now()->subMinutes(5))
                        ->orWhereNull('last_seen');
                });
            } elseif ($status === 'banned') {
                $query->where('status', 0);
            } elseif ($status === 'active') {
                $query->where('status', 1);
            }
        }

        $sort = $request->input('sort', 'desc');
        $query->orderByRaw('id = ? DESC', [Auth::id()])
            ->orderBy('created_at', $sort);

        $perPage = GeneralSetting::first()->records_per_page ?? 10;
        $staffs = $query->paginate($perPage)->withQueryString();

        // Calculate performance for each staff member in the current month
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        foreach ($staffs as $staff) {
            $grossSales = Sale::notCancelled()
                ->where(function ($q) use ($staff) {
                    $q->where('user_id', $staff->id)
                      ->orWhere('salesperson_id', $staff->id);
                })
                ->whereBetween('sale_date', [$startDate, $endDate])
                ->sum('receivable_amount');

            $returns = SaleReturn::where('status', '!=', SaleReturn::STATUS_REJECTED)
                ->whereHas('sale', function($q) use ($staff) {
                    $q->notCancelled()
                      ->where(function ($q2) use ($staff) {
                          $q2->where('user_id', $staff->id)
                             ->orWhere('salesperson_id', $staff->id);
                      });
                })
                ->whereBetween('return_date', [$startDate, $endDate])
                ->sum('payable_amount');

            $actualSales = max(0, (float)$grossSales - (float)$returns);

            // Sum total quota across routes (or global quota) for this staff member
            $targetSum = StaffTarget::where('user_id', $staff->id)
                ->where('target_type', 'monthly')
                ->where('start_date', '<=', $endDate)
                ->where('end_date', '>=', $startDate)
                ->sum('target_amount');

            $targetAmount = (float)$targetSum;
            $progress = $targetAmount > 0 ? ($actualSales / $targetAmount) * 100 : 0;

            $dummyTarget = new StaffTarget();

            /** @var StaffTarget|null $target */
            $staff->performance = (object)[
                'target_amount' => $targetAmount,
                'actual_sales' => (float)$actualSales,
                'progress' => round($progress, 2),
                'color' => $targetAmount > 0 ? $dummyTarget->getStatusColor($progress) : ($actualSales > 0 ? 'blue' : 'gray'),
            ];
        }

        $roles = Role::all();

        // Get system appearance primary color
        $systemAppearance = $this->appearance->forSystem();
        $primaryColor = $systemAppearance['colors'][0] ?? '#F15A22';

        return view('staff.index', compact('staffs', 'roles', 'primaryColor'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email',
            'phone' => 'required|string|max:30|unique:users,phone',
            'role_id' => 'required|exists:roles,id',
            'password' => 'required|string|min:8',
        ]);

        $email = $request->filled('email') ? trim($request->email) : null;

        $user = User::create([
            'name' => trim($request->name),
            'email' => $email,
            'phone' => trim($request->phone),
            'password' => Hash::make($request->password),
            'status' => 1,
        ]);

        $user->role_id = $request->role_id;
        $user->save();

        try {
            event(new StaffCreated($user, $request->password));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('StaffCreated event error: ' . $e->getMessage());
        }

        return redirect()->route('staff.index')->with('success', 'Staff added successfully.');
    }

    public function update(Request $request, User $staff)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,'.$staff->id,
            'phone' => 'required|string|max:30|unique:users,phone,'.$staff->id,
            'role_id' => 'required|exists:roles,id',
            'password' => 'nullable|string|min:8',
        ]);

        $email = $request->filled('email') ? trim($request->email) : null;

        $staff->update([
            'name' => trim($request->name),
            'email' => $email,
            'phone' => trim($request->phone),
            'password' => $request->filled('password') ? Hash::make($request->password) : $staff->password,
        ]);

        $staff->role_id = $request->role_id;
        $staff->save();

        \App\Services\PermissionCacheService::invalidateUserPermissions($staff->id);

        return redirect()->route('staff.index')->with('success', 'Staff updated successfully.');
    }

    public function toggleStatus(Request $request, User $staff)
    {
        $staff->status = ! $staff->status;
        $staff->save();

        $status = $staff->status ? 'Activated' : 'Deactivated';

        return redirect()->back()->with('success', "Staff status changed to $status.");
    }

    public function getStatuses()
    {
        $users = User::all(['id', 'last_seen', 'status']);
        $statuses = $users->mapWithKeys(function ($user) {
            $status = 'offline';
            if ($user->status == 0) {
                $status = 'banned';
            } elseif ($user->id === Auth::id()) {
                $status = 'you';
            } elseif ($user->isOnline()) {
                $status = 'online';
            }

            return [$user->id => $status];
        });

        return response()->json($statuses);
    }
}
