<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRouteRequest;
use App\Http\Requests\UpdateRouteRequest;
use App\Models\CustomerRoute;
use App\Models\GeneralSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerRouteController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;
        $search = $request->string('search')->trim()->value();
        $status = $request->string('status')->trim()->value();

        $routes = CustomerRoute::query()
            ->withCount('customers')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('from_location', 'like', "%{$search}%")
                        ->orWhere('to_location', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('routes.index', [
            'routes' => $routes,
        ]);
    }

    public function store(StoreRouteRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        if (empty($validated['name'])) {
            $validated['name'] = "{$validated['from_location']} → {$validated['to_location']}";
        }

        CustomerRoute::create($validated);

        return redirect()->route('customer-routes.index')->with('success', 'Delivery route created successfully.');
    }

    public function update(UpdateRouteRequest $request, CustomerRoute $customerRoute): RedirectResponse
    {
        $validated = $request->validated();
        if (empty($validated['name'])) {
            $validated['name'] = "{$validated['from_location']} → {$validated['to_location']}";
        }

        $customerRoute->update($validated);

        return redirect()->route('customer-routes.index')->with('success', 'Delivery route updated successfully.');
    }

    public function destroy(CustomerRoute $customerRoute): RedirectResponse
    {
        if ($customerRoute->customers()->exists()) {
            return redirect()->route('customer-routes.index')->with('error', 'Cannot delete a route that still has customers assigned. Reassign those customers first.');
        }

        $customerRoute->delete();

        return redirect()->route('customer-routes.index')->with('success', 'Delivery route deleted successfully.');
    }
}
