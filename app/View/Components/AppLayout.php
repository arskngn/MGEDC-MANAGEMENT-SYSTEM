<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        if (auth()->check() && auth()->user()->hasRole('salesperson')) {
            return view('layouts.salesperson');
        }
        return view('layouts.app');
    }
}
