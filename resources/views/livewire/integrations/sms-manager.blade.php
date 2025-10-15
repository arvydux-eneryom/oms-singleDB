<div>
    @if ($successMessage)
        <div class="mb-4 p-4 rounded-xl bg-green-100 text-green-800 border border-green-300 flex items-center justify-between"
             x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 10000)"
             wire:key="success-{{ $messageTimestamp }}">
            <span>{{ $successMessage }}</span>
            <button wire:click="clearMessages" class="ml-4 text-green-600 hover:text-green-800 font-bold">&times;</button>
        </div>
    @endif

    @if ($errorMessage)
        <div class="mb-4 p-4 rounded-xl bg-red-100 text-red-800 border border-red-300 flex items-center justify-between"
             x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 10000)"
             wire:key="error-{{ $messageTimestamp }}">
            <span>{{ $errorMessage }}</span>
            <button wire:click="clearMessages" class="ml-4 text-red-600 hover:text-red-800 font-bold">&times;</button>
        </div>
    @endif

    <div class="min-h-screen bg-gray-100 p-6">
        <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Send SMS Form -->
            <div class="bg-white p-6 rounded-2xl shadow-md">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Send SMS</h2>
                    @if ($accountBalance)
                        @if ($accountBalance === 'Balance unavailable' || $accountBalance === 'Unable to fetch balance')
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-orange-600">⚠️ Balance check unavailable</span>
                                <a href="https://console.twilio.com/us1/billing/manage-billing/billing-overview"
                                   target="_blank"
                                   class="text-xs text-blue-500 hover:underline">
                                    Check Console
                                </a>
                            </div>
                        @else
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-600">Balance:</span>
                                <span class="text-lg font-bold text-blue-600">{{ $accountBalance }}</span>
                                <button wire:click="refreshBalance" type="button"
                                        class="text-gray-400 hover:text-gray-600 text-xl"
                                        title="Refresh balance">
                                    ↻
                                </button>
                            </div>
                        @endif
                    @endif
                </div>

                <form class="space-y-4">
                    <!-- General Warning -->
                    @if (session('warning'))
                        <div class="mb-4 p-4 rounded-xl bg-yellow-100 text-yellow-800 border border-yellow-300">
                            {{ session('warning') }}
                        </div>
                    @endif

                    <!-- Phone Number -->
                    <div>
                        <label class="block text-gray-600 mb-1">Recipient Number</label>
                        <input type="text" wire:model="to" placeholder="+370 600 00000"
                               class="w-full p-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500">
                        @error('to')
                        <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Message -->
                    <div>
                        <label class="block text-gray-600 mb-1">Message</label>
                        <textarea wire:model.live="body" rows="4" maxlength="160"
                                  class="w-full p-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500"
                                  placeholder="Type your SMS message..."></textarea>
                        <p class="text-sm text-gray-500 mt-1">{{ $this->charactersLeft }} / 160 characters</p>
                        @error('body')
                        <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Send Button -->
                    <button type="button" wire:click="sendSingleSms"
                            class="w-full bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-xl font-medium">
                        Send SMS
                    </button>
                </form>

            </div>

            <!-- SMS History -->
            <div class="bg-white p-6 rounded-2xl shadow-md" wire:poll.2s="loadSentSmsMessages">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">SMS History</h2>

                <table class="w-full text-left text-sm">
                    <thead>
                    <tr class="border-b text-gray-600">
                        <th class="py-2">Recipient</th>
                        <th class="py-2">Message</th>
                        <th class="py-2">Status</th>
                        <th class="py-2">Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $statusColors = [
                            'delivered' => 'bg-green-100 text-green-700',
                            'undelivered' => 'bg-red-100 text-red-700',
                            'sent' => 'bg-yellow-100 text-yellow-700',
                            'queued' => 'bg-gray-100 text-gray-700',
                        ];
                    @endphp

                    @foreach ($smsMessages as $sms)
   {{--                     {{ count($smsMessages)}}--}}
                        {{-- Ensure  has a default value --}}
                        @php $sms['status'] = $sms['status'] ?? 'sent'; @endphp
                        <tr class="border-b" wire:key="{{ $sms['id'] }}">
                            <td class="py-2">{{ $sms['to'] }}</td>
                            <td class="py-2">{{ Str::limit($sms['body'], 20) }}</td>
                            <td class="py-2"><span class="px-2 py-1 text-xs rounded {{ $statusColors[$sms['status']] }}">
                                    {{ ucfirst($sms['status'])}}</span></td>
                            <td class="py-2">{{ \Carbon\Carbon::parse($sms['updated_at'])->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        </div>
        <div class="bg-white p-6 rounded-2xl shadow-md mt-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">User List</h2>

            <!-- Search Bar -->
            <div class="mb-4">
                <div>
                    <label class="block text-gray-600 mb-1">Message</label>
                    <textarea wire:model.live="bodyForBulkSms" rows="4" maxlength="160"
                              class="w-full p-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500"
                              placeholder="Type your SMS message..."></textarea>
                    <p class="text-sm text-gray-500 mt-1">{{ $this->charactersLeftBulk }} / 160 characters</p>
                    @error('bodyForBulkSms')
                    <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <table class="w-full text-left text-sm">
                <thead>
                <tr class="border-b text-gray-600">
                    <th class="py-2"><input type="checkbox">
                    </th>
                    <th class="py-2">Name</th>
                    <th class="py-2">Phone</th>
                    <th class="py-2">Status</th>
                    <th class="py-2 text-right">Action</th>
                </tr>
                </thead>
                <tbody>
                <tr class="border-b">
                    <td class="py-2"><input type="checkbox" wire:model="selectedUsers" value="+37064626008"></td>
                    <td class="py-2">Arvydas Kavaliauskas</td>
                    <td class="py-2">+37064626008</td>
                    <td class="py-2"><span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Active</span>
                    </td>
                    <td class="py-2 text-right">
                        <button wire:click="sendSingleSmsFromBulk('+37064626008')" type="button" class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-xs">Send SMS
                        </button>
                    </td>
                </tr>
                <tr class="border-b">
                    <td class="py-2"><input type="checkbox" wire:model="selectedUsers" value="+37065670928"></td>
                    <td class="py-2">Arvydas's friend</td>
                    <td class="py-2">+37065670928</td>
                    <td class="py-2"><span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Active</span>
                    </td>
                    <td class="py-2 text-right">
                        <button wire:click="sendSingleSmsFromBulk('+37065670928')" type="button" class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-xs">Send SMS
                        </button>
                    </td>
                </tr>
                <tr class="border-b">
                    <td class="py-2"><input type="checkbox" wire:model="selectedUsers" value="+37067160181"></td>
                    <td class="py-2">Animesh Chowdhury</td>
                    <td class="py-2">+37067160181</td>
                    <td class="py-2"><span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Active</span>
                    </td>
                    <td class="py-2 text-right">
                        <button wire:click="sendSingleSmsFromBulk('+37067160181')" type="button" class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-xs">Send SMS
                        </button>
                    </td>
                </tr>
                <tr class="border-b">
                    <td class="py-2"><input type="checkbox"></td>
                    <td class="py-2">Tomas Žukauskas</td>
                    <td class="py-2">+37069998765</td>
                    <td class="py-2"><span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Inactive</span>
                    </td>
                    <td class="py-2 text-right">
                        <button disabled class="px-3 py-1 bg-gray-300 text-gray-500 rounded-lg text-xs">Send SMS
                        </button>
                    </td>
                </tr>
                <tr class="border-b">
                    <td class="py-2"><input type="checkbox"></td>
                    <td class="py-2">Monika Jankauskaitė</td>
                    <td class="py-2">+37060022233</td>
                    <td class="py-2"><span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Active</span>
                    </td>
                    <td class="py-2 text-right">
                        <button class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-xs">Send SMS
                        </button>
                    </td>
                </tr>
                <tr>
                    <td class="py-2"><input type="checkbox"></td>
                    <td class="py-2">Laura Stankevičienė</td>
                    <td class="py-2">+37064455666</td>
                    <td class="py-2"><span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Active</span>
                    </td>
                    <td class="py-2 text-right">
                        <button class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-xs">Send SMS
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>

            <!-- Bulk Action -->
            <div class="mt-4">
                <button type="button" wire:click="sendBulkSms" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Send SMS to Selected</button>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-md mt-6">
            <div class="w-1/2">
                <div class="flex items-center space-x-2">
                    <input type="text" wire:model="questionTo" placeholder="+370 600 00000"
                           class="flex-1 p-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500">
                    <button type="button" wire:click="sendQuestion"
                            class="bg-blue-500 hover:bg-blue-600 text-white py-3 px-6 rounded-xl font-medium">
                        Send Question
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
