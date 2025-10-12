<div>
    <div class="main-container">
        <div class="connecting-message">
            <div class="loader"></div>
            <div class="text">Connecting to Telegram server&nbsp;…</div>
        </div>
    </div>
    {{ session()->put('showing-connection-message-for:user-' . Auth::id(), true) }}
    <style>
        .main-container {
            background: #f6fafd;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .connecting-message {
            background: #fff;
            padding: 2rem 3rem;
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .loader {
            border: 4px solid #e0e0e0;
            border-top: 4px solid #0088cc;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            100% { transform: rotate(360deg); }
        }
        .text {
            font-size: 1.3rem;
            color: #0088cc;
            font-weight: 500;
        }
    </style>
    <script>
        window.location.href = "{{ route('integrations.telegram.index') }}";
    </script>
</div>
