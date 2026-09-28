<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AppearanceSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PageAppearanceController extends Controller
{
    public function __construct(
        protected AppearanceSettingService $appearance
    ) {}

    public function index()
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        $personal = $this->appearance->personalForUser($user);
        $siteDefaults = $this->appearance->siteDefaults();
        $isAdmin = $user->hasRole('admin');

        return view('settings.page-appearance', compact('personal', 'siteDefaults', 'isAdmin'));
    }

    public function updatePersonal(Request $request)
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        $system = $this->appearance->validateAppearanceInput($request->all(), 'system_');

        $this->appearance->savePersonal($user, [
            'system_scheme' => $system['system_scheme'],
            'system_colors' => $system['system_colors'],
            'system_button_color' => $system['system_button_color'] ?? null,
        ]);

        return back()->with('success', 'Your system color scheme settings were saved.');
    }

    public function updateSiteDefaults(Request $request)
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        if (! $user->hasRole('admin')) {
            abort(403, 'Only administrators can update site-wide defaults.');
        }

        $system = $this->appearance->validateAppearanceInput($request->all(), 'default_system_');

        $this->appearance->saveSiteDefaults([
            'default_system_scheme' => $system['default_system_scheme'],
            'default_system_colors' => $system['default_system_colors'],
            'default_system_button_color' => $system['default_system_button_color'] ?? null,
        ]);

        return back()->with('success', 'Site-wide system color scheme defaults were saved.');
    }
}
