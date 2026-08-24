@php
    $p = $p ?? [];
@endphp
@extends('storefront.layouts.theme')

@section('title', 'Privacy Policy — '.$p['Business Name'])

@section('content')
@include('storefront.legal.open')
<header>
    <h1>Privacy Policy</h1>
    <p class="legal-meta">
        <span>Last updated {{ $p['Last Updated Date'] }}</span>
        <span aria-hidden="true">·</span>
        <span>{{ $p['Business Name'] }}</span>
    </p>
</header>

<h2>1. Introduction</h2>
<p>{{ $p['Business Name'] }} (“we”, “us”, “our” or “Business”) respects your privacy and is committed to handling your personal information responsibly.</p>
<p>This Privacy Policy explains how {{ $p['Business Name'] }} collects, uses, stores, shares and protects personal information when you visit <a href="{{ $p['Website URL'] }}">this website</a>, make an appointment, purchase a product or service, contact us, or otherwise interact with our business.</p>
<p>This website may be created, hosted or technically supported using technology provided by EasyGrox. EasyGrox provides website and technology infrastructure; {{ $p['Business Name'] }} remains responsible for its own business operations and customer relationships.</p>

<h2>2. Business Information</h2>
<dl class="legal-dl">
    <div><dt>Business name</dt><dd>{{ $p['Business Name'] }}</dd></div>
    <div><dt>Business type</dt><dd>{{ $p['Business Type'] }}</dd></div>
    <div><dt>Address</dt><dd>{{ $p['Business Address'] }}</dd></div>
    <div><dt>Phone</dt><dd>{{ $p['Business Phone'] }}</dd></div>
    <div><dt>Email</dt><dd>{{ $p['Business Email'] }}</dd></div>
    <div><dt>Website</dt><dd><a href="{{ $p['Website URL'] }}">this website</a></dd></div>
</dl>

<h2>3. Information We May Collect</h2>
<p>Depending on how you use our website and services, we may collect:</p>
<ul>
    <li>Name</li>
    <li>Mobile number</li>
    <li>Email address</li>
    <li>Address or location information where necessary</li>
    <li>Appointment and booking details</li>
    <li>Service preferences</li>
    <li>Membership or package information</li>
    <li>Purchase and transaction information</li>
    <li>Reviews and feedback</li>
    <li>Information provided when contacting us</li>
    <li>Information necessary to provide or improve our services</li>
    <li>Technical information such as IP address, browser, device and website activity</li>
</ul>
<p>We only request information that is reasonably necessary for the relevant purpose or otherwise permitted by applicable law.</p>

<h2>4. Appointment and Booking Information</h2>
<p>When you make an appointment, we may collect information necessary to arrange and manage the appointment, including your name, contact details, selected service, preferred date and time, and other information reasonably required to provide the service.</p>
<p>We may use this information to confirm, modify, cancel or remind you about your appointment.</p>

<h2>5. Service-Related Information</h2>
<p>Certain beauty, wellness or personal-care services may require information relevant to the safe or appropriate provision of the service.</p>
<p>Where reasonably necessary, we may ask about allergies, sensitivities, pregnancy, medication, previous reactions or other relevant information.</p>
<p>You should provide accurate information where it is relevant to your safety or the suitability of a service.</p>
<p>We will handle such information in accordance with applicable law and our business procedures.</p>

<h2>6. How We Use Personal Information</h2>
<p>We may use personal information to:</p>
<ul>
    <li>Provide and manage appointments and services.</li>
    <li>Process orders and payments.</li>
    <li>Communicate with customers.</li>
    <li>Send appointment confirmations and reminders.</li>
    <li>Respond to enquiries and support requests.</li>
    <li>Manage memberships and packages.</li>
    <li>Provide customer service.</li>
    <li>Maintain business records.</li>
    <li>Improve our services and website.</li>
    <li>Conduct analytics and understand website usage.</li>
    <li>Prevent fraud, misuse and security incidents.</li>
    <li>Comply with applicable legal obligations.</li>
    <li>Protect our rights, property, customers and business.</li>
</ul>

<h2>7. Marketing Communications</h2>
<p>Where permitted by applicable law, we may use your contact information to send promotional communications about our services, offers, events or products.</p>
<p>You may opt out of promotional communications by using the unsubscribe or opt-out mechanism provided in the communication or by contacting us.</p>
<p>We may continue to send essential service-related communications, such as appointment confirmations, payment information, security notices or important account information.</p>

<h2>8. Cookies and Similar Technologies</h2>
<p>Our website may use cookies and similar technologies for:</p>
<ul>
    <li>Website functionality</li>
    <li>Security</li>
    <li>Preferences</li>
    <li>Analytics</li>
    <li>Performance monitoring</li>
    <li>Improving user experience</li>
    <li>Measuring marketing effectiveness</li>
</ul>
<p>You may be able to control cookies through your browser or device settings. Disabling certain cookies may affect website functionality.</p>
<p>Where third-party analytics or advertising technologies are used, they may process information according to their own policies and applicable law.</p>

<h2>9. Payment Information</h2>
<p>Payments may be processed through third-party payment providers.</p>
<p>We may receive transaction information such as payment status, transaction reference, amount, date and refund status.</p>
<p>Unless otherwise stated, we do not intend to store complete debit-card or credit-card numbers or card security codes on our own systems.</p>
<p>Payment providers process payment information according to their own terms and privacy policies.</p>

<h2>10. How We Share Information</h2>
<p>We may share personal information where reasonably necessary to operate our business and provide services.</p>
<p>This may include sharing information with:</p>
<ul>
    <li>Staff and authorized personnel.</li>
    <li>Service providers.</li>
    <li>Website and hosting providers.</li>
    <li>Payment providers.</li>
    <li>Email, SMS or messaging providers.</li>
    <li>Booking or scheduling providers.</li>
    <li>Analytics providers.</li>
    <li>Technology and IT service providers.</li>
    <li>Professional advisers where necessary.</li>
    <li>Government authorities or law-enforcement agencies where legally required.</li>
</ul>
<p>We do not sell personal information merely because you use our website or services.</p>

<h2>11. EasyGrox Technology Provider</h2>
<p>This website may be created, hosted, maintained or supported using EasyGrox technology.</p>
<p>EasyGrox may process certain information as a technology/service provider to enable website, booking, customer-management, analytics or other functionality.</p>
<p>Where EasyGrox processes information on our behalf, its processing is governed by applicable agreements, its Privacy Policy and applicable law.</p>
<p>EasyGrox does not independently determine the services, prices, appointment policies or business practices of {{ $p['Business Name'] }}.</p>

<h2>12. Third-Party Services and Links</h2>
<p>Our website may contain links to or integrations with third-party services, including payment gateways, Google services, social-media platforms, maps, messaging services, analytics tools and other technology providers.</p>
<p>Third-party services have their own privacy policies and terms. We are not responsible for the privacy practices of independent third-party services.</p>

<h2>13. Data Security</h2>
<p>We take reasonable technical and organizational measures designed to protect personal information against unauthorized access, misuse, loss, alteration or disclosure.</p>
<p>However, no website, internet transmission or electronic storage system can be guaranteed to be completely secure.</p>
<p>Customers should also take reasonable steps to protect their account credentials and personal information.</p>

<h2>14. Data Retention</h2>
<p>We may retain personal information for as long as reasonably necessary to:</p>
<ul>
    <li>Provide services.</li>
    <li>Maintain business and transaction records.</li>
    <li>Provide customer support.</li>
    <li>Resolve disputes.</li>
    <li>Prevent fraud and abuse.</li>
    <li>Meet legal, accounting, tax or regulatory requirements.</li>
</ul>
<p>When information is no longer required, we may delete, anonymize or securely dispose of it, subject to applicable law and legitimate business requirements.</p>

<h2>15. Customer Rights</h2>
<p>Depending on applicable law, you may have rights relating to your personal information, which may include:</p>
<ul>
    <li>Requesting access to information.</li>
    <li>Requesting correction of inaccurate information.</li>
    <li>Requesting deletion where legally applicable.</li>
    <li>Withdrawing consent where processing is based on consent.</li>
    <li>Raising a privacy-related grievance.</li>
    <li>Exercising other rights available under applicable law.</li>
</ul>
<p>Requests may be subject to identity verification and applicable legal limitations.</p>
<p>To exercise a privacy right, please contact us using the details provided below.</p>

<h2>16. Children’s Privacy</h2>
<p>Our services are primarily intended for general customers and businesses. We do not knowingly seek to collect children’s personal information except where permitted and handled in accordance with applicable law.</p>
<p>If you believe that information relating to a child has been provided to us improperly, please contact us.</p>

<h2>17. Accuracy of Information</h2>
<p>You are responsible for providing accurate information when making appointments, purchases or enquiries.</p>
<p>If information you provide changes, please contact us or update your information where the relevant functionality is available.</p>

<h2>18. Reviews, Photos and Customer Content</h2>
<p>If you voluntarily submit reviews, photographs, testimonials, comments or other content, we may use that content for business purposes where permitted by applicable law and subject to any permissions or terms applicable to the submission.</p>
<p>You should not submit another person’s personal information, photograph or content without appropriate authority or permission.</p>

<h2>19. Business Communications</h2>
<p>We may contact you by telephone, SMS, email, WhatsApp or other communication methods where appropriate for appointment management, customer support, transactions, service-related information or permitted marketing.</p>
<p>Communication methods may depend on the contact information and preferences you provide.</p>

<h2>20. Changes to This Privacy Policy</h2>
<p>We may update this Privacy Policy from time to time to reflect changes in our services, technology, business practices or applicable law.</p>
<p>The updated version will be published on this website with a revised “Last Updated” date.</p>

<h2>21. Governing Law</h2>
<p>This Privacy Policy is intended for our operations in India and shall be interpreted subject to applicable laws of India.</p>
<p>Nothing in this Privacy Policy is intended to exclude or restrict any privacy right or obligation that cannot lawfully be excluded or restricted.</p>

<h2>22. Privacy Complaints and Contact</h2>
<p>If you have a question, request or complaint concerning the handling of your personal information, please contact us first.</p>
<p><strong>{{ $p['Business Name'] }}</strong></p>
<p>Address: {{ $p['Business Address'] }}<br>
Email: {{ $p['Business Email'] }}<br>
Phone: {{ $p['Business Phone'] }}<br>
Privacy / grievance contact: {{ $p['Privacy Contact Name or Designation'] }}<br>
Privacy email: {{ $p['Privacy Email'] }}<br>
Customer support hours: {{ $p['Support Hours'] }}</p>
<p>We will review and respond to privacy-related requests in accordance with applicable law.</p>

<h2>23. Important Notice About EasyGrox</h2>
<p>{{ $p['Business Name'] }} independently operates its business and is responsible for the products, services, appointments and customer relationships offered through this website.</p>
<p>EasyGrox provides technology and website infrastructure used by this website. Unless expressly agreed otherwise, EasyGrox does not independently provide the beauty, wellness, personal-care or other services offered by {{ $p['Business Name'] }}.</p>
<p>Questions or complaints concerning our services should generally be directed to {{ $p['Business Name'] }}.</p>

<h2>24. Acceptance</h2>
<p>By using this website, making an appointment, purchasing a product or service, or otherwise interacting with {{ $p['Business Name'] }}, you acknowledge that you have read this Privacy Policy and understand how your information may be handled as described above.</p>
<p class="legal-signoff"><strong>{{ $p['Business Name'] }}</strong><br>{{ $p['Business Tagline'] }}</p>
@include('storefront.legal.close')
@endsection
