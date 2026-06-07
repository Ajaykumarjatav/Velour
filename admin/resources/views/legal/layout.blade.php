<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title') — Velour</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  body { font-family: 'DM Sans', sans-serif; color: #1C1915; background: #FDFCFA; }
  h1,h2,h3 { font-family: 'Playfair Display', serif; }
  .prose h2 { font-size: 1.5rem; font-weight: 700; margin: 2.5rem 0 1rem; color: #0F0E0C; }
  .prose h3 { font-size: 1.125rem; font-weight: 600; margin: 1.5rem 0 .5rem; color: #2A2520; }
  .prose p  { line-height: 1.75; margin-bottom: 1rem; color: #4A4540; }
  .prose ul { list-style: disc; padding-left: 1.5rem; margin-bottom: 1rem; }
  .prose li { line-height: 1.7; color: #4A4540; margin-bottom: .375rem; }
  .prose a  { color: #B8943A; text-decoration: underline; }
  .gold { color: #B8943A; }
  .nav-link { padding: .5rem 1rem; border-radius: .5rem; font-size: .875rem; font-weight: 500;
    color: #4A4540; transition: background .15s; }
  .nav-link:hover { background: #F5F0E8; color: #0F0E0C; }
  .nav-link.active { background: #F5F0E8; color: #B8943A; }
</style>
</head>
<body>

{{-- Top nav --}}
<header class="border-b border-gray-100 bg-white sticky top-0 z-10">
  <div class="max-w-5xl mx-auto px-6 h-14 flex items-center justify-between">
    <a href="{{ route('dashboard') }}" class="text-xl font-bold" style="font-family:'Playfair Display',serif;color:#0F0E0C">
      Velour<span class="gold">.</span>
    </a>
    <div class="flex items-center gap-1">
      <a href="{{ route('legal.privacy') }}" class="nav-link @yield('nav-active-privacy')">Privacy</a>
      <a href="{{ route('legal.terms') }}" class="nav-link @yield('nav-active-terms')">Terms</a>
      <a href="{{ route('legal.cookies') }}" class="nav-link @yield('nav-active-cookies')">Cookies</a>
    </div>
  </div>
</header>

<main class="max-w-3xl mx-auto px-6 py-16">
  <div class="mb-10">
    <p class="text-sm font-medium uppercase tracking-widest gold mb-3">@yield('doc-type')</p>
    <h1 class="text-4xl font-bold text-gray-900 mb-2">@yield('title')</h1>
    <p class="text-gray-400 text-sm">Last updated: @yield('last-updated')</p>
  </div>
  <article class="prose max-w-none">
    @yield('content')
  </article>
</main>

<footer class="border-t border-gray-100 py-8 mt-16">
  <p class="text-center text-sm text-gray-400">
    © {{ date('Y') }} Velour Salon SaaS. All rights reserved.
  </p>
</footer>

</body>
</html>
