<?php

namespace App\Livewire\Tenancy\Customers;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View as RenderView;
use Livewire\Component;

class View extends Component
{
    public Customer $customer;

    public string $currentTab = 'overview';

    public array $activityLog = [];

    public array $stats = [];

    // Quick action modals
    public bool $showDeleteModal = false;

    public bool $showSmsModal = false;

    public bool $showEmailModal = false;

    // SMS/Email form data
    public string $smsMessage = '';

    public string $emailSubject = '';

    public string $emailBody = '';

    public ?string $selectedPhone = null;

    public ?string $selectedEmail = null;

    public function mount(Customer $customer)
    {
        $this->customer = $customer->load([
            'customerPhones',
            'customerEmails',
            'customerContacts',
            'customerServiceAddresses',
            'customerBillingAddresses',
        ]);

        $this->loadActivityLog();
        $this->loadStatistics();

        // Pre-select first phone and email
        /** @var \App\Models\CustomerPhone|null $firstPhone */
        $firstPhone = $this->customer->customerPhones->first();
        $this->selectedPhone = $firstPhone?->phone;

        /** @var \App\Models\CustomerEmail|null $firstEmail */
        $firstEmail = $this->customer->customerEmails->first();
        $this->selectedEmail = $firstEmail?->email;
    }

    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
    }

    // Toggle customer status
    public function toggleStatus(): void
    {
        try {
            $this->customer->update([
                'status' => ! $this->customer->status,
            ]);

            $statusText = $this->customer->status ? 'activated' : 'deactivated';
            session()->flash('success', "Customer successfully $statusText.");

            $this->customer->refresh();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update customer status.');
            logger()->error('Status toggle failed', [
                'error' => $e->getMessage(),
                'customer_id' => $this->customer->id,
            ]);
        }
    }

    // Load activity log (mock data for now - you can implement actual activity tracking)
    private function loadActivityLog(): void
    {
        // This would typically come from an activity_logs table
        // For now, we'll use customer timestamps as activity
        $this->activityLog = [
            [
                'type' => 'created',
                'description' => 'Customer record created',
                'user' => 'System',
                'timestamp' => $this->customer->created_at,
                'icon' => 'plus',
                'color' => 'green',
            ],
        ];

        if ($this->customer->updated_at != $this->customer->created_at) {
            $this->activityLog[] = [
                'type' => 'updated',
                'description' => 'Customer information updated',
                'user' => 'System',
                'timestamp' => $this->customer->updated_at,
                'icon' => 'pencil',
                'color' => 'blue',
            ];
        }

        // Sort by newest first
        usort($this->activityLog, function ($a, $b) {
            return $b['timestamp']->timestamp - $a['timestamp']->timestamp;
        });
    }

    // Load customer statistics
    private function loadStatistics(): void
    {
        $this->stats = [
            'phones_count' => $this->customer->customerPhones->count(),
            'emails_count' => $this->customer->customerEmails->count(),
            'contacts_count' => $this->customer->customerContacts->count(),
            'service_addresses_count' => $this->customer->customerServiceAddresses->count(),
            'billing_addresses_count' => $this->customer->customerBillingAddresses->count(),
            'sms_enabled_phones' => $this->customer->customerPhones->where('is_sms_enabled', true)->count(),
            'verified_emails' => $this->customer->customerEmails->where('is_verified', true)->count(),
        ];
    }

    // Open SMS modal
    public function openSmsModal(?string $phone = null): void
    {
        /** @var \App\Models\CustomerPhone|null $firstPhone */
        $firstPhone = $this->customer->customerPhones->first();
        $this->selectedPhone = $phone ?? $firstPhone?->phone;
        $this->smsMessage = '';
        $this->showSmsModal = true;
    }

    // Send SMS (placeholder - implement with your SMS service)
    public function sendSms(): void
    {
        $this->validate([
            'selectedPhone' => 'required',
            'smsMessage' => 'required|max:1600',
        ]);

        try {
            // Implement your SMS sending logic here
            // Example: app(SmsService::class)->send($this->selectedPhone, $this->smsMessage);

            session()->flash('success', 'SMS sent successfully to '.$this->selectedPhone);
            $this->showSmsModal = false;
            $this->reset(['smsMessage', 'selectedPhone']);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send SMS. Please try again.');
            logger()->error('SMS send failed', [
                'error' => $e->getMessage(),
                'customer_id' => $this->customer->id,
                'phone' => $this->selectedPhone,
            ]);
        }
    }

    // Open email modal
    public function openEmailModal(?string $email = null): void
    {
        /** @var \App\Models\CustomerEmail|null $firstEmail */
        $firstEmail = $this->customer->customerEmails->first();
        $this->selectedEmail = $email ?? $firstEmail?->email;
        $this->emailSubject = '';
        $this->emailBody = '';
        $this->showEmailModal = true;
    }

    // Send email (placeholder - implement with your email service)
    public function sendEmail(): void
    {
        $this->validate([
            'selectedEmail' => 'required|email',
            'emailSubject' => 'required|max:255',
            'emailBody' => 'required',
        ]);

        try {
            // Implement your email sending logic here
            // Example: Mail::to($this->selectedEmail)->send(new CustomerEmail(...));

            session()->flash('success', 'Email sent successfully to '.$this->selectedEmail);
            $this->showEmailModal = false;
            $this->reset(['emailSubject', 'emailBody', 'selectedEmail']);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send email. Please try again.');
            logger()->error('Email send failed', [
                'error' => $e->getMessage(),
                'customer_id' => $this->customer->id,
                'email' => $this->selectedEmail,
            ]);
        }
    }

    // Delete customer
    public function delete(): void
    {
        try {
            DB::transaction(function () {
                $this->customer->delete();
            });

            session()->flash('success', 'Customer deleted successfully.');
            $this->redirectRoute('customers.index', navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete customer. Please try again.');
            logger()->error('Customer delete failed', [
                'error' => $e->getMessage(),
                'customer_id' => $this->customer->id,
            ]);
        }
    }

    public function render(): RenderView
    {
        return view('livewire.tenancy.customers.view');
    }
}
