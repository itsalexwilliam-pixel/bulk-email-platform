<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe — {{ config('app.name', 'Novelio Technologies') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 40px 32px;
            max-width: 460px;
            width: 100%;
            text-align: center;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        }
        .icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .icon.success { background: #ecfdf5; }
        .icon.info    { background: #eff6ff; }
        .icon svg { width: 28px; height: 28px; }
        h1 {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 10px;
        }
        p {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
        }
        .brand {
            margin-top: 32px;
            font-size: 12px;
            color: #94a3b8;
        }
        .brand strong { color: #64748b; }
    </style>
</head>
<body>
    <div class="card">

        @if(str_contains(strtolower($message), 'unsubscribed') || str_contains(strtolower($message), 'removed'))
            <div class="icon success">
                <svg fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1>You've been unsubscribed</h1>
        @else
            <div class="icon info">
                <svg fill="none" stroke="#6366f1" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1>Subscription Update</h1>
        @endif

        <p>{{ $message }}</p>

        <p class="brand">
            &copy; {{ date('Y') }} <strong>{{ config('app.name', 'Novelio Technologies') }}</strong>. All rights reserved.
        </p>
    </div>
</body>
</html>
