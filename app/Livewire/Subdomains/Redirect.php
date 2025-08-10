<?php

namespace App\Livewire\Subdomains;

use App\Models\Domain;
use Livewire\Component;

class Redirect extends Component
{
    public function render()
    {
        $subdomains = Domain::with(['tenant' => function ($query) {
            $query->withCount('users');
        }])
            ->where('system_id', auth()->user()->system_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.subdomains.redirect', compact('subdomains'));
    }
}
