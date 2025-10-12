<div>
    @php
        libxml_use_internal_errors(true); // Suppress warnings
    @endphp

    <!-- Loading Overlay -->
    <div wire:loading wire:target="logoutFromTelegram" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-8 flex flex-col items-center gap-4 shadow-2xl">
            <svg class="spinner-icon w-12 h-12 text-[#229ED9]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-lg font-semibold text-gray-800">Logging out...</p>
        </div>
    </div>

    @if(session('warning'))
        <div
            class="flex items-center p-4 mb-4 text-sm text-yellow-800 rounded-xl bg-yellow-50 border border-yellow-200 shadow-md"
            role="alert">
            <svg class="w-6 h-6 mr-3" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="12" fill="#FACC15"/>
                <path d="M12 7v5m0 4h.01" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="sr-only">Warning</span>
            <div class="font-semibold">
                {{ session('warning') }}
            </div>
        </div>
    @endif

    @if(session('message'))
        <div
            class="flex items-center p-4 mb-4 text-sm text-green-700 rounded-xl bg-green-50 border border-green-200 shadow-md"
            role="alert">
            <svg class="w-6 h-6 mr-3" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="12" fill="#2CA65A"/>
                <path d="M17 9l-5.5 6L7 11.5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="sr-only">Success</span>
            <div class="font-semibold">
                {{ session('message') }}
            </div>
        </div>
    @endif

    @if(session('success'))
        <div
            class="flex items-center p-4 mb-4 text-sm text-green-700 rounded-xl bg-green-50 border border-green-200 shadow-md"
            role="alert">
            <svg class="w-6 h-6 mr-3" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="12" fill="#2CA65A"/>
                <path d="M17 9l-5.5 6L7 11.5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="sr-only">Success</span>
            <div class="font-semibold">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if($telegramAuthState === 3)
        <!--  3 - Logged in-->
        <div class="container max-w-2xl ">
            <!-- Telegram Logo -->
            <div style="display: flex; align-items: center; gap: 12px;">
                <img src="{{ asset('images/telegram-logo.svg') }}" alt="Telegram Logo" class="logo"
                     style="margin-bottom: 0;"/>
                <h1 style="margin: 0;">Telegram dashboard</h1>
            </div>

            <div class="bg-white shadow-md rounded-2xl p-6 mt-4 flex items-center justify-between">
                <!-- Left: Avatar + Info -->
                <div class="flex items-center gap-4">
                    <div
                        class="w-16 h-16 rounded-full bg-[#229ED9] text-white flex items-center justify-center text-2xl font-bold">
                        {{ strtoupper(substr($telegramLoggedUserData['first_name'], 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">
                            {{ $telegramLoggedUserData['first_name'] ?? '' }} {{ $telegramLoggedUserData['last_name'] ?? '' }}
                        </h2>
                        @if (isset($telegramLoggedUserData['username']) && $telegramLoggedUserData['username'])
                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-[#e6f4ff] text-[#229ED9] font-mono text-base font-semibold">
                                <span>@</span>{{ $telegramLoggedUserData['username'] }}
                            </span>
                        @endif
                        <p class="text-gray-600 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="currentColor"
                                 viewBox="0 0 24 24">
                                <path
                                    d="M6.62 10.79a15.91 15.91 0 006.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1v3.5c0 .55-.45 1-1 1C9.61 21 3 14.39 3 6.5 3 5.95 3.45 5.5 4 5.5H7.5c.55 0 1 .45 1 1 0 1.24.2 2.45.57 3.57.12.35.03.75-.24 1.02l-2.21 2.2z"/>
                            </svg>
                            {{ $telegramLoggedUserData['phone'] ?? '' }}
                        </p>
                        <span class="inline-block mt-2 px-3 py-1 bg-green-100 text-[#2CA65A] text-xs rounded-full">
                    ✅ Active Telegram Session
                    </span>
                    </div>
                </div>
                <!-- Right: Actions -->
                <div class="flex items-center gap-2">
                    <button
                        wire:click="logoutFromTelegram"
                        type="button"
                        class="logout-btn px-4 py-2 rounded-lg border border-[#229ED9] text-[#229ED9] hover:bg-[#229ED9] hover:text-white transition flex items-center justify-center"
                        wire:loading.attr="disabled"
                        wire:target="logoutFromTelegram">
                        Log out
                    </button>
                </div>
            </div>

            <!-- Create Channel -->
            <div class="bg-white shadow-md rounded-2xl p-6 mt-3">
                <h3 class="text-lg font-semibold text-gray-800 mb-5 flex items-center gap-2">
                    <span class="text-[#229ED9] text-xl">＋</span>
                    Create a New Channel
                </h3>

                <form wire:submit="createChannel" class="space-y-4">
                    @csrf
                    <div>
                        <input type="text" name="channel_name" id="title" wire:model="title" maxlength="128"
                               placeholder="Channel Name"
                               class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#229ED9] outline-none"
                               required>
                        @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
        <textarea name="description" id="description" wire:model="description" maxlength="255" placeholder="Channel Description"
          class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#229ED9] outline-none" required></textarea>
                        <p class="text-xs text-gray-400 mt-1">This will appear in your channel’s about section.</p>
                        @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit"
                            class="px-6 py-2 bg-[#229ED9] text-white rounded-lg hover:bg-[#0088cc] transition transform hover:scale-105">
                        Create Channel
                    </button>
                </form>
            </div>
            <!-- Your Channels -->
            @if(!empty($channels))
                <div class="bg-white shadow-md rounded-2xl p-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-5 flex items-center gap-2">
                        📢 Your Channels
                    </h3>
                    <ul class="space-y-4">
                        @foreach($channels as $channel)
                            <li class="p-4 border rounded-lg flex items-center justify-between hover:bg-gray-50">
                                <div>
                                    <h4 class="font-medium text-gray-800">{{ $channel['title'] }}</h4>
                                    {{--        <p class="text-gray-500 text-sm">{{ $channel['title'] }}</p>--}}
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        onclick="sendInvite({{ $channel['id'] }})"
                                        class="px-4 py-1 text-sm bg-[#229ED9] text-white rounded-md hover:bg-[#0088cc] transition">
                                        Send invite
                                    </button>
                                    <a href="#"
                                       wire:click="sendMessageToChannel({{ $channel['id']  }})"
                                       class="px-4 py-1 text-sm bg-[#229ED9] text-white rounded-md hover:bg-[#0088cc] transition">
                                        Send message
                                    </a>
                                    <button
                                        wire:click.prevent="deleteTelegramChannel('{{ $channel['id'] }}')"
                                        class="px-4 py-1 text-sm bg-red-500 text-white rounded-md hover:bg-red-600 transition">
                                        Delete
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <style>
        :root {
            --dot-size: 10px;
            --dot-color: #5b8def;
            --animation-speed: 0.9s;
        }

        .server-status .dot {
            width: var(--dot-size);
            height: var(--dot-size);
            background: var(--dot-color);
            border-radius: 50%;
            animation: pulse var(--animation-speed) infinite ease-in-out;
        }

        .server-status .dot:nth-child(1) {
            animation-delay: 0s;
        }

        .server-status .dot:nth-child(2) {
            animation-delay: 0.15s;
        }

        .server-status .dot:nth-child(3) {
            animation-delay: 0.3s;
        }

        @keyframes pulse {
            0%, 80%, 100% {
                transform: scale(0.6);
                opacity: 0.5;
            }
            40% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .server-status .text {
            margin-left: 4px;
            white-space: nowrap;
        }

        h1 {
            font-size: 26px;
            margin: 12px 0 24px;
            color: #0088cc;
        }

        .logo {
            width: 56px;
            height: 56px;
            display: block;
            margin-bottom: 10px;
        }

        .warning h3 {
            margin-top: 0;
            font-size: 19px;
        }

        .qr-box img {
            margin: 14px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 6px;
            background: #fff;
            width: 100%;
            max-width: 120px;
        }

        .phone-box input {
            width: 100%;
            max-width: 300px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin: 12px 0;
            font-size: 14px;
        }

        .warning-box h3 {
            margin-top: 0;
            font-size: 18px;
            color: #4a3a12;
        }

        .warning-box p {
            margin: 8px 0;
        }

        .warning-box a {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 12px;
            background: #2A8BF2;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }

        .warning-box a:hover {
            background: #1f6dcc;
        }

        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Hide arrows in Firefox */
        input[type=number] {
            -moz-appearance: textfield;
        }

        /* Spinner animation */
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        .spinner-icon {
            animation: spin 1s linear infinite;
        }
    </style>

    <script>
        function sendInvite(channelId) {
            const username = prompt('Enter Telegram username (with or without @):');
            if (username && username.trim()) {
                @this.sendChannelInviteToUser(channelId, username.trim());
            }
        }
    </script>
</div>
