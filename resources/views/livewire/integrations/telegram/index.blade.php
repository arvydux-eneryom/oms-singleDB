<div >
    @php
        libxml_use_internal_errors(true); // Suppress warnings
    @endphp

    @if(session('error'))
        <div class="flex items-center p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            <span class="sr-only">Error</span>
            <div>
                {{ session('error') }}
            </div>
        </div>
    @endif

    @if(session('warning'))
        <div class="flex items-center p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300" role="alert">
            <svg class="w-5 h-5 mr-2 text-yellow-800 dark:text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10c0 4.418-3.582 8-8 8s-8-3.582-8-8 3.582-8 8-8 8 3.582 8 8zm-8-3a1 1 0 00-.707.293l-3 3a1 1 0 001.414 1.414L10 9.414l2.293 2.293a1 1 0 001.414-1.414l-3-3A1 1 0 0010 7z" clip-rule="evenodd"></path>
            </svg>
            <span class="sr-only">Warning</span>
            <div>
                {{ session('warning') }}
            </div>
        </div>
    @endif

    @if(session('message'))
        <div class="flex items-center p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-300" role="alert">
            <svg class="w-5 h-5 mr-2 text-blue-800 dark:text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10c0 4.418-3.582 8-8 8s-8-3.582-8-8 3.582-8 8-8 8 3.582 8 8zm-1-4a1 1 0 10-2 0v4a1 1 0 002 0V6zm-1 8a1.5 1.5 0 110-3 1.5 1.5 0 010 3z" clip-rule="evenodd"></path>
            </svg>
            <span class="sr-only">Info</span>
            <div>
                {{ session('message') }}
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="flex items-center p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
            <svg class="w-5 h-5 mr-2 text-green-800 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-11a1 1 0 112 0v4a1 1 0 01-2 0V7zm1 8a1.5 1.5 0 110-3 1.5 1.5 0 010 3z" clip-rule="evenodd"></path>
            </svg>
            <span class="sr-only">Success</span>
            <div>
                {{ session('success') }}
            </div>
        </div>
    @endif
    <div class="container max-w-2xl ">
        <!-- Telegram Logo -->
        <div style="display: flex; align-items: center; gap: 12px;">
            <img src="{{ asset('images/telegram-logo.svg') }}" alt="Telegram Logo" class="logo" style="margin-bottom: 0;" />
            <h1 style="margin: 0;">Telegram Integration</h1>
        </div>

        <div class="card warning">
            <h3>Not registered on Telegram yet?</h3>
            <p>To continue, please download Telegram and create an account. It only takes a minute.</p>
            <a href="https://telegram.org" target="_blank" rel="noopener noreferrer" class="btn">Download Telegram</a>
        </div>

        <div class="status">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" fill="none" stroke="#0088cc" stroke-width="2"/>
                <line x1="12" y1="8" x2="12" y2="8" stroke="#0088cc" stroke-width="2" stroke-linecap="round"/>
                <line x1="12" y1="12" x2="12" y2="16" stroke="#0088cc" stroke-width="2" stroke-linecap="round"/>
            </svg>
            Not logged in. Please complete the login process.
        </div>
        <div class="steps">
            @if($telegramAuthState !== 1 && $telegramAuthState !== 3)
            <div class="card qr-box">
                <div class="section-title">Option 1: Scan QR Code</div>
                <p>Open the Telegram app on your phone and scan this code to log in:</p>
              <div wire:init="getQrCode" wire:poll.3s="getQrCode">
                    @if($qrSvg)
                        <div>{!! $qrSvg !!}</div>
                    @else
                        <div class="flex items-center justify-center py-6">
                            <svg class="animate-spin h-8 w-8 text-[#0088cc]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    @endif
                </div>
            </div>

                <div class="card phone-box">
                    <div class="section-title">Option 2: Or enter your phone number</div>
                    <form wire:submit="sendPhoneNumber" autocomplete="off">
                        <div class="flex flex-col gap-y-2">
                            <label for="phone" class="font-medium">Phone number</label>
                            <input
                                type="tel"
                                id="phone"
                                wire:model="phone"
                                class="border rounded w-full p-2 my-3"
                                required
                                autocomplete="tel"
                                inputmode="tel"
                            >
                            @error('phone')
                            <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                        <button
                            type="submit"
                            class="btn"
                            wire:loading.attr="disabled"
                        >Send phone number</button>
                    </form>

                    <div wire:loading wire:target="sendPhoneNumber"
                         class="flex items-center gap-3 my-3 max-w-xl p-4 bg-[#f8fbff] border border-[#d0e4ff] rounded-xl shadow-sm text-blue-900 font-medium">
                        <svg class="w-6 h-6 text-[#0088cc] animate-pulse shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240" fill="currentColor">
                            <path d="M120 0C53.7 0 0 53.7 0 120s53.7 120 120 120 120-53.7 120-120S186.3 0 120 0zm58.6 83.1l-22.2 104.9c-1.7 7.4-6.2 9.2-12.6 5.7l-35-25.8-16.9 16.3c-1.9 1.9-3.6 3.6-7.3 3.6l2.6-36.6 66.5-59.9c2.9-2.6-0.6-4.1-4.5-1.5l-82.2 51.7-35.5-11.1c-7.7-2.4-7.9-7.7 1.6-11.4l138.5-53.5c6.4-2.3 12 1.5 9.9 11z"/>
                        </svg>

                        <div>
                            <p>Sending phone number  to <span class="font-semibold text-[#0088cc]">Telegram</span>… Please wait.</p>
                            <div class="flex gap-1 mt-2">
                                <div class="w-2.5 h-2.5 bg-[#0088cc] rounded-full animate-bounce"></div>
                                <div class="w-2.5 h-2.5 bg-[#0088cc] rounded-full animate-bounce [animation-delay:0.2s]"></div>
                                <div class="w-2.5 h-2.5 bg-[#0088cc] rounded-full animate-bounce [animation-delay:0.4s]"></div>
                            </div>
                        </div>
                    </div>

                </div>
            @endif
        </div>
        @if($telegramAuthState === 1)
            <div class="card phone-box ">
                <div class="section-title">Enter login number</div>
                <form wire:submit="sendCompletePhoneLogin">
                    <div class="flex flex-col gap-y-2">
                        <input type="number" id="loginCode"  wire:model="loginCode" class="border rounded w-full p-2  my-3" required >
                        <div wire:loading wire:target="sendCompletePhoneLogin"
                             class="flex items-center gap-3 my-3 max-w-xl p-4 bg-[#f8fbff] border border-[#d0e4ff] rounded-xl shadow-sm text-blue-900 font-medium">
                            <svg class="w-6 h-6 text-[#0088cc] animate-pulse shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240" fill="currentColor">
                                <path d="M120 0C53.7 0 0 53.7 0 120s53.7 120 120 120 120-53.7 120-120S186.3 0 120 0zm58.6 83.1l-22.2 104.9c-1.7 7.4-6.2 9.2-12.6 5.7l-35-25.8-16.9 16.3c-1.9 1.9-3.6 3.6-7.3 3.6l2.6-36.6 66.5-59.9c2.9-2.6-0.6-4.1-4.5-1.5l-82.2 51.7-35.5-11.1c-7.7-2.4-7.9-7.7 1.6-11.4l138.5-53.5c6.4-2.3 12 1.5 9.9 11z"/>
                            </svg>
                            <div>
                                <p>Sending login code to <span class="font-semibold text-[#0088cc]">Telegram</span>… Please wait.</p>
                                <div class="flex gap-1 mt-2">
                                    <div class="w-2.5 h-2.5 bg-[#0088cc] rounded-full animate-bounce"></div>
                                    <div class="w-2.5 h-2.5 bg-[#0088cc] rounded-full animate-bounce [animation-delay:0.2s]"></div>
                                    <div class="w-2.5 h-2.5 bg-[#0088cc] rounded-full animate-bounce [animation-delay:0.4s]"></div>
                                </div>
                            </div>
                        </div>
                        @error('loginCode')
                        <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit" class="btn">Send login number</button>
                </form>
            </div>
        @endif
        <div>

        @if($telegramAuthState === 3) <!--  3 - Logged in-->
                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                    <div class="flex items-center gap-4">
                        <!-- Profile Icon -->
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 bg-blue-500 text-white rounded-full flex items-center justify-center text-2xl font-bold">
                                {{ strtoupper(substr($telegramLoggedUserData['first_name'], 0, 1)) }}
                            </div>
                        </div>
                        <!-- User Info -->
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                                {{ $telegramLoggedUserData['first_name'] }} {{ $telegramLoggedUserData['last_name'] ?? '' }}
                            </h2>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                <svg class="inline w-4 h-4 mr-1 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M3 5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v16l-8-4-8 4V5z"/>
                                </svg>
                                {{ $telegramLoggedUserData['phone'] ?? 'No phone number' }}
                            </p>
                        </div>
                    </div>
                    <!-- Status -->
                    <div class="mt-4 flex items-center gap-2">
                        <span class="text-green-600 text-lg">✅</span>
                        <span class="text-gray-700 dark:text-gray-300">Logged in successfully</span>
                    </div>
                </div>
        @endif
        </div>
    </div>

    <style>
        :root {
            --dot-size: 10px;
            --dot-color: #5b8def;
            --animation-speed: 0.9s;
        }

        .server-status .dot:nth-child(1) { animation-delay: 0s; }
        .server-status .dot:nth-child(2) { animation-delay: 0.15s; }
        .server-status .dot:nth-child(3) { animation-delay: 0.3s; }

        @keyframes pulse {
            0%, 80%, 100% { transform: scale(0.6); opacity: 0.5; }
            40% { transform: scale(1); opacity: 1; }
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
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 22px 26px;
            margin: 16px auto;
            width: 100%;
            max-width: 800px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.05);
        }
        .warning {
            background: #e6f4ff;
            border: 1px solid #b3d7ff;
            color: #094067;
        }
        .warning h3 {
            margin-top: 0;
            font-size: 19px;
        }
        .btn {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 16px;
            background: #0088cc;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.2s ease;
        }
        .btn:hover {
            background: #006fa8;
        }
        .status {
            background: #e6f4ff;
            border: 1px solid #b3d7ff;
            color: #094067;
            padding: 14px 18px;
            border-radius: 8px;
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .status svg {
            flex-shrink: 0;
            width: 20px;
            height: 20px;
        }
        .qr-box, .phone-box {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
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
        .section-title {
            font-weight: 600;
            margin-bottom: 6px;
            color: #0088cc;
            font-size: 15px;
        }
        .steps {
            display: flex;
            flex-direction: column;
            gap: 16px;
            width: 100%;
            max-width: 900px;
            margin: 20px auto;
            position: relative;
        }
        @media (min-width: 768px) {
            .steps {
                flex-direction: row;
                align-items: flex-start;
            }
            .steps .card {
                flex: 1;
                margin: 0;
            }
            .steps::before {
                content: "";
                position: absolute;
                top: 0;
                bottom: 0;
                left: 50%;
                width: 1px;
                background: #e0e6ed;
            }
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
    </style>
</div>
