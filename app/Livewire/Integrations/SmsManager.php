<?php

namespace App\Livewire\Integrations;

use App\Models\SmsMessage;
use App\Services\SmsManagerService;
use App\Services\TwilioSmsService;
use Illuminate\Bus\Batch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class SmsManager extends Component
{
    public $smsMessages = [];
    public $to;
    public string $questionTo = '';
    public $body;
    public $bodyForBulkSms;
    public array $selectedUsers = [];
    public ?string $successMessage = null;
    public ?string $errorMessage = null;
    public ?string $messageTimestamp = null;
    public ?string $accountBalance = null;

    public function mount()
    {
        $this->loadSentSmsMessages();
        $this->loadAccountBalance();
    }

    public function loadAccountBalance($forceRefresh = false)
    {
        $smsService = app(TwilioSmsService::class);

        if ($forceRefresh) {
            cache()->forget('twilio_account_balance');
        }

        // Cache balance for 5 minutes to avoid excessive API calls
        $balance = cache()->remember('twilio_account_balance', 300, function () use ($smsService) {
            return $smsService->getAccountBalance();
        });

        $this->accountBalance = $balance['formatted'];
    }

    public function refreshBalance()
    {
        $this->loadAccountBalance(true);
        $this->successMessage = 'Balance refreshed!';
        $this->messageTimestamp = (string) now()->timestamp;
    }

    public function clearMessages()
    {
        $this->successMessage = null;
        $this->errorMessage = null;
        $this->messageTimestamp = null;
    }

    public function updatedTo($value)
    {
        $this->to = trim($value);
    }

    public function updatedQuestionTo($value)
    {
        $this->questionTo = trim($value);
    }

    public function updatedBody($value)
    {
        $this->body = trim($value);
    }

    public function updatedBodyForBulkSms($value)
    {
        $this->bodyForBulkSms = trim($value);
    }

    public function sendSingleSms()
    {
        $this->validate([
            'to' => 'required|regex:/^\+\d{1,15}$/',
            'body' => 'required|max:160',
        ]);
        $this->send($this->to, $this->body);
        $this->reset(['to', 'body']);
        $this->loadSentSmsMessages();
    }

    public function sendSingleSmsFromBulk(string $to)
    {
        $to = trim($to);
        $this->validate(
            [
                'bodyForBulkSms' => 'required|max:160',
            ],
            [
                'bodyForBulkSms.required' => 'Please enter the SMS message.',
                'bodyForBulkSms.max' => 'The SMS message may not be greater than 160 characters.',
            ]
        );
        $this->send($to, $this->bodyForBulkSms);
        $this->reset(['to', 'bodyForBulkSms']);
        $this->loadSentSmsMessages();
    }

    public function sendBulkSms()
    {
        $this->validate([
            'bodyForBulkSms' => 'required|max:160',
        ]);

        $jobs = [];
        foreach ($this->selectedUsers as $user) {
            $jobs[] = (new \App\Jobs\SendBulkSmsJob(trim($user), $this->bodyForBulkSms, auth()->id()))
                ->delay(now()->addSecond());
        }

        Bus::batch($jobs)
            ->then(function (Batch $batch) {
                // Called when all jobs in the batch have been processed successfully
                Log::info('All bulk SMS jobs completed successfully.');
                session()->flash('success', 'All bulk SMS messages were sent successfully!');
            })
            ->catch(function (Batch $batch, \Throwable $e) {
                // Called if any job in the batch fails
                Log::error('Bulk SMS batch failed.', ['error' => $e->getMessage()]);
                session()->flash('error', 'Some SMS messages failed to send.');
            })
            ->finally(function (Batch $batch) {
                // Called after all jobs (successful or failed) have been processed
                Log::info('Bulk SMS batch processing finished.');
            })
            ->dispatch();

        session()->flash('success', 'Bulk SMS sending process completed!');

        $this->reset(['bodyForBulkSms']);
        $this->loadSentSmsMessages();

        return redirect()->back()->with('success', 'Bulk SMS sending process completed!');
    }

    public function sendQuestion()
    {
        $this->clearMessages();
        $this->validate([
            'questionTo' => 'required|regex:/^\+\d{1,15}$/',
        ]);
        try {
            $smsManagerService = app(SmsManagerService::class);
            $smsManagerService->sendQuestion($this->questionTo, auth()->id());
            Log::info('Question SMS sent successfully', [
                'to' => $this->questionTo,
                'user_id' => auth()->id(),
            ]);
            $this->successMessage = 'Question SMS sent successfully!';
            $this->messageTimestamp = (string) now()->timestamp;
        } catch (\Exception $e) {
            Log::error('Question SMS sending failed', [
                'error' => $e->getMessage(),
                'to' => $this->questionTo,
                'user_id' => auth()->id(),
            ]);
            $this->errorMessage = $e->getMessage();
            $this->messageTimestamp = (string) now()->timestamp;
        }
        $this->reset(['questionTo']);
        $this->loadSentSmsMessages();
    }

    protected function send($to, $body): void
    {
        $this->clearMessages();
        try {
            $smsService = app(TwilioSmsService::class);
            $message = $smsService->send($to, $body, auth()->id());
            Log::info('SMS sent successfully', [
                'to' => $to,
                'body' => $body,
                'user_id' => auth()->id(),
                'sms_sid' => $message->sid,
            ]);
            $this->successMessage = 'SMS sent successfully!';
            $this->messageTimestamp = (string) now()->timestamp;
        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'error' => $e->getMessage(),
                'to' => $to,
                'body' => $body,
                'user_id' => auth()->id(),
            ]);
            $this->errorMessage = $e->getMessage();
            $this->messageTimestamp = (string) now()->timestamp;
        }
    }

    public function getCharactersLeftProperty(): int
    {
        return 160 - mb_strlen($this->body ?? '');
    }

    public function getCharactersLeftBulkProperty(): int
    {
        return 160 - mb_strlen($this->bodyForBulkSms ?? '');
    }

    public function loadSentSmsMessages()
    {
        $this->smsMessages = SmsMessage::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        $this->render();
    }

    public function render()
    {
        return view('livewire.integrations.sms-manager');
    }
}
