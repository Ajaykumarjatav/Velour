@extends('storefront.layouts.theme')

@section('content')
@include('storefront.themes.tattoo.partials.top-bar')
<div class="relative bg-black">
@include('storefront.themes.tattoo.partials.hero')
@include('storefront.themes.tattoo.partials.sticky-nav')
</div>
@include('storefront.themes.tattoo.partials.about')
@include('storefront.partials.dynamic.awards')
@include('storefront.partials.dynamic.services')
@include('storefront.partials.dynamic.packages')
@include('storefront.themes.tattoo.partials.relaxation')
{{-- Special offer banner hidden for now --}}
@include('storefront.partials.dynamic.staff')
@include('storefront.themes.tattoo.partials.premium-banner')
@include('storefront.partials.dynamic.locations')
@include('storefront.partials.dynamic.testimonials')
@include('storefront.themes.tattoo.partials.footer-info-cards')
@include('storefront.themes.tattoo.partials.footer')
@endsection
