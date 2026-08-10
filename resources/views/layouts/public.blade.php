<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title') — EasyGrox</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  body { font-family: 'DM Sans', sans-serif; color: #1C1915; background: #FDFCFA; }
  h1,h2,h3 { font-family: 'Playfair Display', serif; }
  .required-asterisk { color: #ef4444; font-weight: 600; margin-left: 0.15rem; }
</style>
</head>
<body>
<header class="border-b border-gray-100 bg-white sticky top-0 z-10">
  <div class="max-w-5xl mx-auto px-6 h-14 flex items-center justify-between">
    <a href="{{ url('/') }}" class="text-xl font-bold" style="font-family:'Playfair Display',serif;color:#0F0E0C">
      EasyGrox<span style="color:#B8943A">.</span>
    </a>
    <div class="flex items-center gap-3 text-sm">
      <a href="{{ route('help.index') }}" class="text-gray-600 hover:text-gray-900">Help</a>
      @auth
        <a href="{{ \App\Support\SalonUrl::dashboardUrl() }}" class="text-amber-700 font-medium hover:text-amber-800">Dashboard</a>
      @else
        <a href="{{ route('login') }}" class="text-amber-700 font-medium hover:text-amber-800">Sign in</a>
      @endauth
    </div>
  </div>
</header>
@yield('content')
<footer class="border-t border-gray-100 py-8 mt-16">
  <p class="text-center text-sm text-gray-400">© {{ date('Y') }} EasyGrox Salon SaaS. All rights reserved.</p>
</footer>
@include('partials.form-client-validation')
</body>
</html>
