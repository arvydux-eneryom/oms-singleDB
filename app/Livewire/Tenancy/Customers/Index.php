<?php

namespace App\Livewire\Tenancy\Customers;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @property \Illuminate\Contracts\Pagination\LengthAwarePaginator $customers
 */
class Index extends Component
{
    use WithPagination;

    // Search and filters
    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $statusFilter = 'all'; // all, active, inactive

    #[Url]
    public string $sortField = 'created_at';

    #[Url]
    public string $sortDirection = 'desc';

    #[Url]
    public int $perPage = 15;

    // Bulk operations
    public array $selectedCustomers = [];

    public bool $selectAll = false;

    // UI state
    public bool $showFilters = false;

    public bool $showBulkActions = false;

    public string $bulkAction = '';

    // Statistics
    public array $stats = [];

    // Available options
    public array $perPageOptions = [10, 15, 25, 50, 100];

    public array $sortOptions = [
        'created_at' => 'Date Created',
        'company' => 'Company Name',
        'updated_at' => 'Last Updated',
        'status' => 'Status',
    ];

    public function mount()
    {
        $this->loadStatistics();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    // Computed property for selected customer IDs
    #[Computed]
    public function selectedCount(): int
    {
        return count($this->selectedCustomers);
    }

    // Toggle all customers selection
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedCustomers = collect($this->customers->items())->pluck('id')->toArray();
        } else {
            $this->selectedCustomers = [];
        }
    }

    // Clear all selections
    public function clearSelection(): void
    {
        $this->selectedCustomers = [];
        $this->selectAll = false;
    }

    // Toggle sort direction
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    // Reset all filters
    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'all';
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    // Load statistics
    private function loadStatistics(): void
    {
        $tenantId = tenant('id');

        $this->stats = [
            'total' => Customer::where('tenant_id', $tenantId)->count(),
            'active' => Customer::where('tenant_id', $tenantId)->where('status', true)->count(),
            'inactive' => Customer::where('tenant_id', $tenantId)->where('status', false)->count(),
            'this_month' => Customer::where('tenant_id', $tenantId)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }

    // Bulk delete customers
    public function bulkDelete(): void
    {
        if (empty($this->selectedCustomers)) {
            session()->flash('error', 'No customers selected.');

            return;
        }

        try {
            DB::transaction(function () {
                Customer::whereIn('id', $this->selectedCustomers)->delete();
            });

            $count = count($this->selectedCustomers);
            session()->flash('success', "$count customer(s) successfully deleted.");

            $this->clearSelection();
            $this->loadStatistics();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete customers. Please try again.');
            logger()->error('Bulk delete failed', [
                'error' => $e->getMessage(),
                'customer_ids' => $this->selectedCustomers,
            ]);
        }
    }

    // Bulk update status
    public function bulkUpdateStatus(bool $status): void
    {
        if (empty($this->selectedCustomers)) {
            session()->flash('error', 'No customers selected.');

            return;
        }

        try {
            DB::transaction(function () use ($status) {
                Customer::whereIn('id', $this->selectedCustomers)
                    ->update(['status' => $status]);
            });

            $count = count($this->selectedCustomers);
            $statusText = $status ? 'activated' : 'deactivated';
            session()->flash('success', "$count customer(s) successfully $statusText.");

            $this->clearSelection();
            $this->loadStatistics();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update customer status. Please try again.');
            logger()->error('Bulk status update failed', [
                'error' => $e->getMessage(),
                'customer_ids' => $this->selectedCustomers,
                'status' => $status,
            ]);
        }
    }

    // Export to CSV
    public function exportCsv(): StreamedResponse
    {
        $customers = $this->getQueryBuilder()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="customers-'.now()->format('Y-m-d').'.csv"',
        ];

        return response()->stream(function () use ($customers) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'ID',
                'Company',
                'Address',
                'City',
                'Country',
                'Postcode',
                'Status',
                'Phones',
                'Emails',
                'Created At',
            ]);

            // CSV rows
            foreach ($customers as $customer) {
                $customer->load(['customerPhones', 'customerEmails']);

                fputcsv($file, [
                    $customer->id,
                    $customer->company,
                    $customer->address,
                    $customer->city,
                    $customer->country,
                    $customer->postcode,
                    $customer->status ? 'Active' : 'Inactive',
                    $customer->customerPhones->pluck('phone')->implode(', '),
                    $customer->customerEmails->pluck('email')->implode(', '),
                    $customer->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }

    // Export selected customers to CSV
    public function exportSelectedCsv(): StreamedResponse|\Illuminate\Http\Response
    {
        if (empty($this->selectedCustomers)) {
            session()->flash('error', 'No customers selected.');

            return response()->noContent();
        }

        $customers = Customer::with(['customerPhones', 'customerEmails'])
            ->whereIn('id', $this->selectedCustomers)
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="customers-selected-'.now()->format('Y-m-d').'.csv"',
        ];

        return response()->stream(function () use ($customers) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID',
                'Company',
                'Address',
                'City',
                'Country',
                'Postcode',
                'Status',
                'Phones',
                'Emails',
                'Created At',
            ]);

            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->id,
                    $customer->company,
                    $customer->address,
                    $customer->city,
                    $customer->country,
                    $customer->postcode,
                    $customer->status ? 'Active' : 'Inactive',
                    $customer->customerPhones->pluck('phone')->implode(', '),
                    $customer->customerEmails->pluck('email')->implode(', '),
                    $customer->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }

    // Get query builder for customers
    private function getQueryBuilder()
    {
        $tenantId = tenant('id');

        return Customer::query()
            ->where('tenant_id', $tenantId)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('company', 'like', '%'.$this->search.'%')
                        ->orWhere('address', 'like', '%'.$this->search.'%')
                        ->orWhere('city', 'like', '%'.$this->search.'%')
                        ->orWhere('country', 'like', '%'.$this->search.'%')
                        ->orWhereHas('customerPhones', function ($q) {
                            $q->where('phone', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('customerEmails', function ($q) {
                            $q->where('email', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                $query->where('status', $this->statusFilter === 'active');
            })
            ->orderBy($this->sortField, $this->sortDirection);
    }

    // Get customers (computed property)
    #[Computed]
    public function customers()
    {
        return $this->getQueryBuilder()
            ->with(['customerPhones', 'customerEmails'])
            ->paginate($this->perPage);
    }

    public function render(): View
    {
        return view('livewire.tenancy.customers.index');
    }
}
