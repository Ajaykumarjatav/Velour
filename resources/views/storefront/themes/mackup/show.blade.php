@extends('storefront.layouts.theme')

@section('content')
@include('storefront.themes.mackup.partials.top-bar')
@include('storefront.themes.mackup.partials.hero')
@include('storefront.themes.mackup.partials.sticky-nav')
@include('storefront.themes.mackup.partials.about')
@include('storefront.partials.dynamic.awards')
@include('storefront.partials.dynamic.services')
@include('storefront.partials.dynamic.packages')
@include('storefront.themes.mackup.partials.relaxation')
{{-- Special offer banner hidden for now --}}
@include('storefront.partials.dynamic.staff')
@include('storefront.themes.mackup.partials.premium-banner')
@include('storefront.partials.dynamic.locations')
@include('storefront.partials.dynamic.testimonials')
@include('storefront.themes.mackup.partials.footer-info-cards')
@include('storefront.themes.mackup.partials.footer')
@endsection
