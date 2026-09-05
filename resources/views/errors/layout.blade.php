<!DOCTYPE html>
<html lang="en" class="h-full css-pending">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') — EasyGrox</title>
    @include('partials.favicon')
    @include('partials.prevent-fouc-start')
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
<body class="min-h-full bg-gradient-to-br from-gray-50 to-velour-50 flex items-center justify-center p-4 sm:p-6 py-10">
    <div class="text-center max-w-md w-full">
        {{-- Logo --}}
        <a href="{{ url('/') }}" class="inline-flex items-center justify-center mb-8 sm:mb-10">
            <img src="{{ asset('images/easygrox-logo-light.png') }}"
                 alt="EasyGrox"
                 class="h-9 sm:h-10 w-auto max-w-[11rem] object-contain">
        </a>

        {{-- Error code --}}
        <h1 class="text-6xl sm:text-8xl font-black text-velour-600 leading-none mb-4">@yield('code')</h1>

        {{-- Title --}}
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3">@yield('title')</h2>

        {{-- Message --}}
        <p class="text-gray-500 text-sm leading-relaxed mb-8">@yield('message')</p>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            @hasSection('actions')
                @yield('actions')
            @else
                @if(auth()->check())
                <a href="{{ \App\Support\AuthPanel::homeUrl(auth()->user()) }}"
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
            @endif
        </div>
    </div>
    @include('partials.prevent-fouc-end')
</body>
</html>
