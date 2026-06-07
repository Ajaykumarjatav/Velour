<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Unavailable</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-violet-600 via-indigo-700 to-slate-900 text-white">
    <div class="max-w-2xl mx-auto px-4 py-20">
        <div class="rounded-2xl border border-white/20 bg-white/10 backdrop-blur p-8">
            <h1 class="text-2xl font-bold mb-2">{{ $salon->name }}</h1>
            <p class="text-sm text-violet-100 mb-6">Online booking is currently unavailable.</p>

            <ul class="space-y-2 text-sm text-violet-50 mb-6">
                @foreach(($reasons ?? []) as $reason)
                    <li class="flex items-start gap-2">
                        <span class="mt-0.5">•</span>
                        <span>{{ $reason }}</span>
                    </li>
                @endforeach
            </ul>

            <div class="flex flex-wrap gap-3">
                @if(!empty($salon->phone))
                    <a href="tel:{{ $salon->phone }}" class="inline-flex rounded-xl bg-white text-violet-700 px-4 py-2 text-sm font-semibold">Call Salon</a>
                @endif
                @if(!empty($salon->email))
                    <a href="mailto:{{ $salon->email }}" class="inline-flex rounded-xl border border-white/40 px-4 py-2 text-sm font-semibold">Email Salon</a>
                @endif
            </div>
        </div>
    </div>
</body>
</html>

