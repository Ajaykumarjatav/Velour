@php
    $p = $p ?? [];
@endphp
@extends('storefront.layouts.theme')

@section('title', 'Terms & Conditions — '.$p['Business Name'])

@section('content')
@include('storefront.legal.open')
<header>
    <h1>Terms &amp; Conditions</h1>
    <p class="legal-meta">
        <span>Last updated {{ $p['Last Updated Date'] }}</span>
        <span aria-hidden="true">·</span>
        <span>{{ $p['Business Name'] }}</span>
    </p>
</header>

<p>Welcome to <strong>{{ $p['Business Name'] }}</strong>.</p>
<p>These Terms &amp; Conditions (“Terms”) govern your use of <a href="{{ $p['Website URL'] }}">this website</a> and the products, services, appointments, bookings and other services offered by {{ $p['Business Name'] }} (“we”, “us”, “our” or “Business”).</p>
<p>By accessing this website, making an appointment, purchasing a service or product, or otherwise using our website or services, you agree to these Terms.</p>
<p>If you do not agree with these Terms, please do not use this website or our services.</p>

<h2>1. About the Business</h2>
<dl class="legal-dl">
    <div><dt>Business name</dt><dd>{{ $p['Business Name'] }}</dd></div>
    <div><dt>Business type</dt><dd>{{ $p['Business Type'] }}</dd></div>
    <div><dt>Address</dt><dd>{{ $p['Business Address'] }}</dd></div>
    <div><dt>Phone</dt><dd>{{ $p['Business Phone'] }}</dd></div>
    <div><dt>Email</dt><dd>{{ $p['Business Email'] }}</dd></div>
    <div><dt>Website</dt><dd><a href="{{ $p['Website URL'] }}">this website</a></dd></div>
</dl>
<p>{{ $p['Business Name'] }} independently operates its business and is responsible for the services, products, prices, appointments and customer relationships offered through this website.</p>
<p>This website may be technically powered, hosted or maintained using technology provided by EasyGrox. EasyGrox provides website and technology infrastructure and is not the provider of the beauty, wellness, personal-care or other services offered by {{ $p['Business Name'] }}.</p>

<h2>2. Services</h2>
<p>{{ $p['Business Name'] }} may offer services including, but not limited to:</p>
<ul>
    @foreach(['Service Category 1','Service Category 2','Service Category 3','Service Category 4'] as $key)
        @if($p[$key] !== '')
            <li>{{ $p[$key] }}</li>
        @endif
    @endforeach
</ul>
<p>The services, prices, duration, availability and other details displayed on this website may change from time to time.</p>

<h2>3. Appointments and Bookings</h2>
<p>Customers may be able to book appointments through this website.</p>
<p>When making a booking, you agree to provide accurate information, including your name, mobile number, email address, selected service, preferred date and time, and other information reasonably required to complete the booking.</p>
<p>A booking may be considered confirmed only after you receive confirmation from {{ $p['Business Name'] }} or as otherwise indicated on the website.</p>

<h2>4. Appointment Availability</h2>
<p>Appointment availability depends on staff availability, business operating hours, service duration, existing bookings, holidays, closures and other operational circumstances.</p>
<p>Displaying an available time does not necessarily guarantee availability until the booking has been successfully confirmed.</p>

<h2>5. Cancellation and Rescheduling</h2>
<p>Customers may cancel or reschedule an appointment according to our cancellation policy.</p>
<dl class="legal-dl">
    <div><dt>Cancellation period</dt><dd>{{ $p['Cancellation Period'] }}</dd></div>
    <div><dt>Cancellation fee</dt><dd>{{ $p['Cancellation Fee'] }}</dd></div>
    <div><dt>Rescheduling</dt><dd>{{ $p['Rescheduling Policy'] }}</dd></div>
</dl>
<p>The specific cancellation and refund terms applicable to your booking will be communicated at the time of booking or payment.</p>

<h2>6. Late Arrival</h2>
<p>Customers are requested to arrive on time. If you arrive late, we may shorten the service, reschedule the appointment or apply the applicable cancellation or late-arrival policy.</p>

<h2>7. Prices and Payments</h2>
<p>Prices are generally stated in {{ $p['Currency'] }} unless otherwise specified. Prices may change from time to time.</p>
<p>We may accept payments through {{ $p['Payment Method 1'] }}, {{ $p['Payment Method 2'] }} and {{ $p['Payment Method 3'] }}.</p>
<p>Payments may be processed through third-party payment providers. We do not necessarily store complete payment-card information on our systems.</p>

<h2>8. Taxes and Charges</h2>
<p>Applicable taxes, fees or legally required charges may be added where applicable. The final amount payable will be displayed or communicated before completion of the transaction where reasonably possible.</p>

<h2>9. Refunds</h2>
<p>Refunds are subject to our applicable refund policy.</p>
<p><strong>Refund policy:</strong> {{ $p['Refund Policy Summary'] }}</p>
<p>Certain services may be non-refundable once provided or commenced. Nothing in this section limits mandatory rights available under applicable law.</p>

<h2>10. Packages and Memberships</h2>
<p>Where we offer packages, memberships, subscriptions or prepaid services, additional terms may apply, including validity, number of services, transferability, cancellation and refund conditions.</p>

<h2>11. Promotions and Offers</h2>
<p>Promotions may be subject to expiry dates, eligibility requirements, minimum purchase requirements and service restrictions. We may modify or withdraw promotions where permitted by law.</p>

<h2>12. Gift Cards and Vouchers</h2>
<p>If offered, gift cards and vouchers are subject to the conditions communicated at purchase.</p>
<p><strong>Gift card / voucher policy:</strong> {{ $p['Gift Card Policy'] }}</p>

<h2>13. Service Information</h2>
<p>We make reasonable efforts to ensure service descriptions, prices and other information are accurate. Images may be illustrative and may not represent the exact result received.</p>

<h2>14. Beauty, Wellness and Personal-Care Services</h2>
<p>Certain services may involve physical contact, cosmetic procedures, beauty treatments, wellness treatments or other activities that may not be suitable for every individual.</p>
<p>Customers should inform us about relevant allergies, sensitivities, pregnancy, medication, previous reactions or other information that may affect safety or suitability.</p>

<h2>15. Customer Responsibility</h2>
<p>Customers are responsible for providing accurate and relevant information necessary for safe and appropriate services.</p>
<p>We may refuse or modify a service where reasonably necessary for safety, suitability, customer conduct or legal compliance.</p>

<h2>16. Results of Services</h2>
<p>Results of beauty, cosmetic, wellness or personal-care services may vary from person to person. We do not guarantee a particular result unless expressly stated otherwise.</p>

<h2>17. Products</h2>
<p>If products are sold through this website, descriptions, prices and availability may change. Customers should follow manufacturer instructions and review ingredients where relevant.</p>

<h2>18. Customer Accounts</h2>
<p>If an account is required, customers are responsible for accurate information, credential confidentiality, preventing unauthorized access and notifying us of suspected unauthorized access.</p>

<h2>19. Reviews and Feedback</h2>
<p>Customers may submit reviews, ratings, comments or feedback. Reviews should be genuine, non-misleading and lawful. We may moderate or remove content that violates these Terms or applicable law.</p>

<h2>20. Website Content</h2>
<p>The website may contain text, images, videos, logos, graphics, service descriptions, prices, offers, reviews and business information. We make reasonable efforts to keep information accurate but do not guarantee that all information is always complete, current or error-free.</p>

<h2>21. Intellectual Property</h2>
<p>Unless otherwise stated, content, branding, logos, photographs, designs and materials belonging to {{ $p['Business Name'] }} are protected by applicable intellectual-property laws. Third-party trademarks remain the property of their respective owners.</p>

<h2>22. Website Availability</h2>
<p>The website may occasionally be unavailable due to maintenance, updates, technical problems, hosting problems, internet failures, security incidents, third-party service failures or circumstances beyond our reasonable control.</p>

<h2>23. Third-Party Services and Links</h2>
<p>The website may contain links or integrations with payment providers, Google services, social-media platforms, maps, messaging services, booking services and analytics services. Third-party services are governed by their own terms and privacy policies.</p>

<h2>24. Privacy</h2>
<p>Your use of this website is also subject to our <a href="{{ $p['Privacy Policy URL'] }}">Privacy Policy</a>.</p>

<h2>25. Communications</h2>
<p>By providing your mobile number or email address, you may receive appointment confirmations, reminders, cancellations, rescheduling notices, payment/order information and customer-support communications. Promotional communications may also be sent where permitted by applicable law.</p>

<h2>26. Prohibited Use</h2>
<p>You must not use this website to commit fraud, misuse another person’s information, attempt unauthorized access, introduce malicious software, disrupt website operations, submit false information, abuse employees or staff, engage in unlawful activity or violate another person’s rights.</p>

<h2>27. Limitation of Responsibility</h2>
<p>To the maximum extent permitted by applicable law, {{ $p['Business Name'] }} will not be responsible for indirect or consequential losses arising from use of this website or services.</p>
<p>Nothing in these Terms excludes or limits liability that cannot legally be excluded or limited under applicable law.</p>

<h2>28. Events Outside Our Control</h2>
<p>We will not be responsible for failure or delay caused by circumstances beyond our reasonable control, including natural disasters, fire, flood, government action, civil unrest, war, power failures, internet or telecommunications failures, third-party service failures, epidemics, pandemics or other unforeseen events.</p>

<h2>29. EasyGrox Technology Provider</h2>
<p>This website may be created, hosted, maintained or supported using technology provided by EasyGrox.</p>
<p>EasyGrox is a technology and website service provider and does not independently provide the beauty, wellness, personal-care or other services offered by {{ $p['Business Name'] }}.</p>
<p>Unless expressly stated otherwise:</p>
<ul>
    <li>EasyGrox is not the seller of our services.</li>
    <li>EasyGrox is not responsible for appointment disputes.</li>
    <li>EasyGrox is not responsible for refunds owed by {{ $p['Business Name'] }}.</li>
    <li>EasyGrox is not responsible for the quality of services provided by {{ $p['Business Name'] }}.</li>
    <li>EasyGrox is not responsible for statements made by {{ $p['Business Name'] }}.</li>
    <li>EasyGrox is not responsible for disputes between {{ $p['Business Name'] }} and its customers.</li>
</ul>

<h2>30. Changes to These Terms</h2>
<p>We may update these Terms from time to time. The updated version will be published with a revised “Last Updated” date.</p>

<h2>31. Governing Law</h2>
<p>These Terms shall be governed by the applicable laws of India.</p>
<p>Subject to applicable law, disputes shall be subject to the jurisdiction of courts having appropriate jurisdiction over {{ $p['Business City'] }}, {{ $p['Business State'] }}, India.</p>

<h2>32. Complaints and Customer Support</h2>
<p><strong>{{ $p['Business Name'] }}</strong></p>
<p>Address: {{ $p['Business Address'] }}<br>
Phone: {{ $p['Business Phone'] }}<br>
Email: {{ $p['Business Email'] }}<br>
Customer support hours: {{ $p['Support Hours'] }}</p>
<p>We will make reasonable efforts to review and respond to customer complaints within a reasonable period.</p>

<h2>33. General Terms</h2>
<p>If any provision is invalid or unenforceable, the remaining provisions will continue to apply to the extent permitted by law.</p>
<p>These Terms, together with the Privacy Policy and any specific service, booking, refund or membership terms, govern your relationship with {{ $p['Business Name'] }}.</p>

<h2>34. Contact Information</h2>
<p><strong>{{ $p['Business Name'] }}</strong><br>
Address: {{ $p['Business Address'] }}<br>
Phone: {{ $p['Business Phone'] }}<br>
Email: {{ $p['Business Email'] }}<br>
Website: <a href="{{ $p['Website URL'] }}">this website</a></p>

<aside class="legal-notice">
    <h2>Important website notice</h2>
    <p>{{ $p['Business Name'] }} operates independently from EasyGrox. EasyGrox provides the technology and website infrastructure used by this website but does not provide or control the products and services offered by {{ $p['Business Name'] }}.</p>
    <p>By using this website, you agree to these Terms &amp; Conditions.</p>
    <p class="legal-signoff"><strong>{{ $p['Business Name'] }}</strong><br>{{ $p['Business Tagline'] }}</p>
</aside>
@include('storefront.legal.close')
@endsection
