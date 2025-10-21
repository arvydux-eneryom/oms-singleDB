<?php

namespace App\Services;

use App\Models\Domain;

class RedirectionToSubdomainService
{
    public static function redirectToSubdomain()
    {
        $subdomains = Domain::with(['tenant' => function ($query) {
            $query->withCount('users');
        }])
            ->where('system_id', auth()->user()->system_id)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($subdomains->count() === 1) {
            return redirect()->route('subdomains.redirect');
        }
        else {
            return redirect()->route('dashboard');
        }
    }

    public static function getRedirectRouteName(): string
    {
        $subdomains = Domain::with(['tenant' => function ($query) {
            $query->withCount('users');
        }])
            ->where('system_id', auth()->user()->system_id)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($subdomains->count() === 1) {
            return 'subdomains.redirect';
        }
        else {
            return 'dashboard';
        }
    }
}
