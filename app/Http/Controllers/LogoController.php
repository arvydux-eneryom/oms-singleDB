<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class LogoController extends Controller
{
    public function uploadLogo(Request $request, Company $company)
    {
        $request->validate([
            'logo' => 'required|image|max:2048',
        ], [
            'logo.max' => 'The logo must not exceed 2MB in size.',
        ]);

        $company->clearMediaCollection('logo'); // Optional: clear previous logo

        $company->addMediaFromRequest('logo')
            ->withResponsiveImages()
            ->toMediaCollection('logo');

        return back()->with('success', 'Logo uploaded successfully.');
    }
}
