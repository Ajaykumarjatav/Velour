@extends('storefront.layouts.theme')

@section('content')
@include('storefront.themes.pet-grooming.partials.top-bar')
@include('storefront.themes.pet-grooming.partials.hero')
@include('storefront.themes.pet-grooming.partials.sticky-nav')
@include('storefront.themes.pet-grooming.partials.about')
@include('storefront.partials.dynamic.awards')
@include('storefront.partials.dynamic.services')
@include('storefront.partials.dynamic.packages')
@include('storefront.themes.pet-grooming.partials.relaxation')
{{-- Special offer banner hidden for now --}}
@include('storefront.partials.dynamic.staff')
@include('storefront.themes.pet-grooming.partials.premium-banner')
@include('storefront.partials.dynamic.locations')
@include('storefront.partials.dynamic.testimonials')
@include('storefront.themes.pet-grooming.partials.footer-info-cards')
@include('storefront.themes.pet-grooming.partials.footer')
@endsection
