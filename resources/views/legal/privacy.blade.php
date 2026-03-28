@extends('legal.layout')
@section('doc-type', 'Legal')
@section('title', 'Privacy Policy')
@section('last-updated', '6 March 2026')
@section('nav-active-privacy', 'active')

@section('content')

<h2>1. Who We Are</h2>
<p>Velour Salon SaaS ("Velour", "we", "us") is operated by Velour Technologies Ltd, registered in England and Wales. Our registered address and Data Protection Officer contact: <a href="mailto:privacy@velour.app">privacy@velour.app</a>.</p>

<h2>2. Data We Collect</h2>

<h3>Account data</h3>
<p>Name, email address, hashed password, phone number, profile photo, and billing information (stored by Stripe — we never hold full card numbers).</p>

<h3>Salon &amp; usage data</h3>
<p>Salon name, address, opening hours, services, staff details, appointment history, and client records you create within the platform.</p>

<h3>Technical data</h3>
<p>IP address, browser user agent, session identifiers, and usage analytics (page views, feature interactions) to operate and improve the service.</p>

<h3>Client data (data processor role)</h3>
<p>When you enter your clients' personal data into Velour, you are the data controller. We process that data on your behalf under our <a href="{{ route('legal.terms') }}">Data Processing Agreement</a> (incorporated into the Terms of Service).</p>

<h2>3. Legal Basis for Processing</h2>
<ul>
  <li><strong>Contract</strong> — processing necessary to deliver the subscription service you signed up for.</li>
  <li><strong>Legitimate interests</strong> — fraud detection, security monitoring, product improvement.</li>
  <li><strong>Legal obligation</strong> — financial records, tax compliance, responding to lawful requests.</li>
  <li><strong>Consent</strong> — marketing emails (you may withdraw at any time).</li>
</ul>

<h2>4. How We Use Your Data</h2>
<ul>
  <li>Provide, maintain, and improve the Velour platform</li>
  <li>Process subscription payments via Stripe</li>
  <li>Send transactional emails (booking confirmations, reminders, billing receipts)</li>
  <li>Detect and prevent fraud, abuse, and security threats</li>
  <li>Comply with legal obligations (HMRC, ICO, court orders)</li>
  <li>Send product updates and tips (marketing, only with consent)</li>
</ul>

<h2>5. Data Sharing</h2>
<p>We do not sell your data. We share data only with:</p>
<ul>
  <li><strong>Stripe</strong> — payment processing (Stripe Privacy Policy applies)</li>
  <li><strong>AWS / Cloudflare</strong> — hosting and CDN infrastructure</li>
  <li><strong>Twilio / SendGrid</strong> — SMS and email delivery</li>
  <li><strong>Legal authorities</strong> — when required by law</li>
</ul>

<h2>6. Data Retention</h2>
<ul>
  <li>Active account data: retained for the life of your subscription + 30 days</li>
  <li>Financial/billing records: 7 years (UK tax law)</li>
  <li>GDPR erasure audit trail: 7 years</li>
  <li>Security audit logs: 1 year</li>
  <li>Deleted accounts: soft-deleted immediately, hard-deleted after 30 days</li>
</ul>

<h2>7. Your Rights (GDPR)</h2>
<p>Under UK GDPR and EU GDPR, you have the right to:</p>
<ul>
  <li><strong>Access</strong> — request a copy of your data</li>
  <li><strong>Rectification</strong> — correct inaccurate data</li>
  <li><strong>Erasure</strong> — request deletion of your personal data</li>
  <li><strong>Portability</strong> — receive your data in a structured, machine-readable format</li>
  <li><strong>Restriction</strong> — limit how we process your data</li>
  <li><strong>Objection</strong> — object to processing based on legitimate interests</li>
</ul>
<p>To exercise any right, email <a href="mailto:privacy@velour.app">privacy@velour.app</a>. We will respond within 30 days. You also have the right to lodge a complaint with the ICO (ico.org.uk).</p>

<h2>8. Cookies</h2>
<p>We use essential, functional, and analytics cookies. See our <a href="{{ route('legal.cookies') }}">Cookie Policy</a> for full details and your consent preferences.</p>

<h2>9. International Transfers</h2>
<p>Data is processed in the UK and EU. Any transfer outside the UK/EEA is subject to Standard Contractual Clauses (SCCs) or an adequacy decision.</p>

<h2>10. Security</h2>
<p>We protect your data using TLS 1.3 in transit, AES-256 encryption at rest, 2FA, role-based access controls, and regular penetration testing.</p>

<h2>11. Changes to This Policy</h2>
<p>We will notify you by email at least 14 days before any material changes. Continued use after the effective date constitutes acceptance.</p>

<h2>12. Contact</h2>
<p>Data Protection Officer: <a href="mailto:privacy@velour.app">privacy@velour.app</a><br>
Velour Technologies Ltd, 123 Example Street, London, EC1A 1BB</p>

@endsection
