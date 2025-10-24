<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserLogoController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'logo.required' => 'Please select a logo to upload.',
            'logo.image' => 'The file must be an image.',
            'logo.mimes' => 'The logo must be a file of type: jpeg, png, jpg, gif, svg.',
            'logo.max' => 'The logo must not exceed 2MB in size.',
        ]);

        try {
            $user = Auth::user();

            // Determine collection based on context
            $collection = tenant() ? 'tenant_logo' : 'system_logo';

            // Clear previous logo for this scope
            $user->clearMediaCollection($collection);

            // Upload new logo to scope-specific collection
            $user->addMedia($request->file('logo'))
                ->usingFileName($request->file('logo')->getClientOriginalName())
                ->toMediaCollection($collection);

            return redirect()->back()->with('logo-uploaded', 'Logo uploaded successfully!');
        } catch (\Exception $e) {
            \Log::error('Logo upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()->with('logo-error', 'The logo failed to upload. Please try again.');
        }
    }
}
