<?php

namespace App\Livewire\Subdomains;

use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public function mount() {}

    public function delete(int $id)
    {
        $subDomain = Domain::findOrFail($id);

        if ($subDomain->tenant) {
            // Detach all users from the tenant
            if ($subDomain->tenant->users()) {
                $userIds = $subDomain->tenant->users()->pluck('id')->toArray();
                $subDomain->tenant->users()->detach();
                // Delete the tenant
                DB::table('tenant_user')->where('tenant_id', $subDomain->tenant->id)->delete();
                Tenant::where('id', $subDomain->tenant->id)->delete();
            }
        }
        // Delete the domain
        $subDomain->delete();

        session()->flash('success', 'Subdomain and all it\'s users successfully deleted.');
    }

    public function render()
    {
        $subdomains = Domain::with(['tenant' => function ($query) {
            $query->withCount('users');
        }])
            ->where('system_id', auth()->user()->system_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.subdomains.index', compact('subdomains'));
    }
}
