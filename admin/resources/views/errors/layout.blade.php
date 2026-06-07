<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') — Velour</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        velour: { 50:'#f5f3ff',100:'#ede9fe',500:'#8b5cf6',600:'#7c3aed',700:'#6d28d9' }
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-full bg-gradient-to-br from-gray-50 to-velour-50 flex items-center justify-center p-6">
    <div class="text-center max-w-md w-full">
        {{-- Logo --}}
        <div class="inline-flex items-center gap-2 mb-10">
            <div class="w-8 h-8 bg-velour-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
            </div>
            <span class="font-bold text-gray-900">Velour</span>
        </div>

        {{-- Error code --}}
        <h1 class="text-8xl font-black text-velour-600 leading-none mb-4">@yield('code')</h1>

        {{-- Title --}}
        <h2 class="text-2xl font-bold text-gray-900 mb-3">@yield('title')</h2>

        {{-- Message --}}
        <p class="text-gray-500 text-sm leading-relaxed mb-8">@yield('message')</p>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            @if(auth()->check())
            <a href="{{ route('dashboard') }}"
               class="px-6 py-3 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
                Back to Dashboard
            </a>
            @else
            <a href="{{ route('login') }}"
               class="px-6 py-3 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
                Sign In
            </a>
            @endif
            <a href="javascript:history.back()"
               class="px-6 py-3 text-sm font-medium rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-600 transition-colors">
                Go Back
            </a>
        </div>
    </div>
</body>
</html>
