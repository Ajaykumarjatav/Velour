@extends('legal.layout')
@section('doc-type', 'Legal')
@section('title', 'Cookie Policy')
@section('last-updated', '6 March 2026')
@section('nav-active-cookies', 'active')

@section('content')

<h2>1. What Are Cookies?</h2>
<p>Cookies are small text files placed on your device when you visit a website. They are used to recognise your browser, remember your preferences, and improve your experience.</p>

<h2>2. Cookies We Use</h2>

<h3>Essential Cookies (always active)</h3>
<p>These are required for the Service to function. You cannot opt out of these.</p>
<ul>
  <li><strong>velour_session</strong> — Maintains your login session. Expires when browser closes.</li>
  <li><strong>XSRF-TOKEN</strong> — Prevents cross-site request forgery. Session cookie.</li>
  <li><strong>cookie_consent</strong> — Records your cookie preferences. 1 year.</li>
</ul>

<h3>Functional Cookies (optional)</h3>
<p>Enhance usability and remember your preferences.</p>
<ul>
  <li><strong>velour_ui_prefs</strong> — Sidebar state, calendar view, table column preferences. 90 days.</li>
  <li><strong>velour_timezone</strong> — Your detected/selected timezone. 1 year.</li>
</ul>

<h3>Analytics Cookies (optional, with consent)</h3>
<p>Help us understand how the platform is used so we can improve it. All data is aggregated and anonymised.</p>
<ul>
  <li><strong>_velour_analytics</strong> — Page views, feature usage, session duration. 1 year.</li>
</ul>

<h3>Marketing Cookies (optional, with consent)</h3>
<p>Used to measure the effectiveness of our marketing campaigns. Never shared with advertisers.</p>
<ul>
  <li><strong>_velour_ref</strong> — Tracks which channel you came from (e.g. Google, referral link). 30 days.</li>
</ul>

<h2>3. Third-Party Cookies</h2>
<ul>
  <li><strong>Stripe</strong> — Sets cookies for payment security and fraud prevention. Stripe's cookie policy applies.</li>
</ul>

<h2>4. Managing Your Preferences</h2>
<p>You can change your cookie consent at any time using the button below, or through your browser settings. Note that disabling essential cookies will prevent you from logging in.</p>

<div class="mt-6 mb-6">
  <button onclick="document.dispatchEvent(new Event('open-cookie-banner'))"
    class="bg-amber-500 text-white px-6 py-3 rounded-xl font-semibold hover:bg-amber-600 transition text-sm">
    Manage Cookie Preferences
  </button>
</div>

<h2>5. More Information</h2>
<p>For more about how we use personal data, see our <a href="{{ route('legal.privacy') }}">Privacy Policy</a>. For questions about cookies, email <a href="mailto:privacy@velour.app">privacy@velour.app</a>.</p>

@endsection
