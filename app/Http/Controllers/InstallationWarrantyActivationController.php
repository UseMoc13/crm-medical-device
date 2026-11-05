<?php

namespace App\Http\Controllers;

use App\Models\Installation;
use App\Services\InstallationWarrantyActivationService;
use Illuminate\Http\RedirectResponse;

class InstallationWarrantyActivationController extends Controller
{
    public function store(Installation $installation, InstallationWarrantyActivationService $service): RedirectResponse
    {
        $result = $service->activate($installation);

        return redirect()->route('installations.show', $installation)->with(
            'success',
            "Warranty activation completed: {$result['created']} created, {$result['existing']} already registered."
        );
    }
}
