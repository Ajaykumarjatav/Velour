@extends('legal.layout')
@section('doc-type', 'Legal')
@section('title', 'Terms of Service')
@section('last-updated', '6 March 2026')
@section('nav-active-terms', 'active')

@section('content')

<h2>1. Acceptance</h2>
<p>By registering for or using Velour Salon SaaS ("Service"), you agree to these Terms of Service ("Terms") and our Privacy Policy. If you are using the Service on behalf of a business, you represent that you have authority to bind that business.</p>

<h2>2. Description of Service</h2>
<p>Velour provides cloud-based salon management software including appointment booking, point-of-sale, client management, staff management, marketing tools, and reporting. Features vary by subscription plan.</p>

<h2>3. Accounts &amp; Registration</h2>
<ul>
  <li>You must provide accurate information when creating an account.</li>
  <li>You are responsible for maintaining the confidentiality of your password and for all activity under your account.</li>
  <li>You must be at least 18 years old to create an account.</li>
  <li>You must notify us immediately of any unauthorised access at <a href="mailto:security@velour.app">security@velour.app</a>.</li>
</ul>

<h2>4. Subscriptions &amp; Billing</h2>
<ul>
  <li>Subscriptions are billed monthly or annually in advance via Stripe.</li>
  <li>Your free trial period is {{ config('billing.trial_days', 14) }} days. No credit card required during trial.</li>
  <li>After trial, a valid payment method is required to continue using paid features.</li>
  <li>You may cancel at any time. Cancellation takes effect at the end of the current billing period. No partial refunds.</li>
  <li>We may change pricing with 30 days' notice. You may cancel before the new price takes effect.</li>
  <li>Disputed charges must be raised within 60 days of the charge.</li>
</ul>

<h2>5. Data Processing Agreement</h2>
<p>As a data processor under GDPR Article 28, Velour agrees to:</p>
<ul>
  <li>Process client personal data only on your documented instructions</li>
  <li>Ensure staff authorised to process data are bound by confidentiality</li>
  <li>Implement appropriate technical and organisational security measures</li>
  <li>Assist you in responding to data subject rights requests</li>
  <li>Delete or return all personal data upon termination of the service</li>
  <li>Provide all information necessary to demonstrate compliance</li>
</ul>

<h2>6. Acceptable Use</h2>
<p>You agree not to:</p>
<ul>
  <li>Use the Service for any unlawful purpose or in violation of any regulations</li>
  <li>Attempt to gain unauthorised access to any part of the Service</li>
  <li>Transmit viruses, malware, or other malicious code</li>
  <li>Reverse engineer or attempt to extract source code</li>
  <li>Use automated scraping, bots, or crawlers without written permission</li>
  <li>Resell, sublicense, or provide white-label access without a written reseller agreement</li>
</ul>

<h2>7. Intellectual Property</h2>
<p>Velour and its licensors retain all rights to the Service, its design, code, and brand. These Terms grant you a limited, non-exclusive, non-transferable licence to use the Service for your internal business purposes.</p>
<p>You retain ownership of all data you upload or create within the Service.</p>

<h2>8. Availability &amp; SLA</h2>
<p>We target 99.9% monthly uptime for the Growth plan and above. Scheduled maintenance windows will be announced 48 hours in advance. Actual uptime is published at <strong>status.velour.app</strong>. Downtime credits are available upon request where SLA is breached.</p>

<h2>9. Limitation of Liability</h2>
<p>To the maximum extent permitted by law, Velour's total liability to you for any claim arising out of or relating to these Terms or the Service shall not exceed the greater of (a) £100 or (b) the fees you paid in the three months preceding the claim.</p>
<p>Velour shall not be liable for indirect, incidental, special, consequential, or punitive damages, including loss of profits, data, or business opportunity.</p>

<h2>10. Termination</h2>
<p>We may suspend or terminate your account immediately if you breach these Terms, engage in fraudulent activity, or for non-payment after a 7-day grace period. Upon termination, your data is retained for 30 days (for recovery purposes) then permanently deleted.</p>

<h2>11. Governing Law</h2>
<p>These Terms are governed by the laws of England and Wales. Disputes shall be subject to the exclusive jurisdiction of the courts of England and Wales.</p>

<h2>12. Contact</h2>
<p>Legal enquiries: <a href="mailto:legal@velour.app">legal@velour.app</a></p>

@endsection
