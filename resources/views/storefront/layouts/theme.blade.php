<!DOCTYPE html>
<html lang="en" class="css-pending">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', ($data['salon']['name'] ?? $salon->name).' — Book Online')</title>
    @include('partials.favicon')
    @include('partials.prevent-fouc-start')
    @include('partials.easygrox-http')
    @if(!empty($data['salon']['description']))
    <meta name="description" content="{{ $data['salon']['description'] }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&family=Pacifico&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ \App\Support\StorefrontAssets::cssUrl($theme) }}">
    <style>
        :root {
            {!! \App\Support\StorefrontAssets::cssVariables($theme) !!}
        }
        [x-cloak] { display: none !important; }
        /* scroll-padding keeps section anchors clear of the nav once it goes fixed. */
        html {
            scroll-behavior: smooth;
            scroll-padding-top: 5rem;
        }
        /* Theme CSS does not always zero the browser default body margin — that
           shows up as a thin white strip around the brown top bar. */
        html, body {
            margin: 0;
            padding: 0;
        }
        body {
            overflow-x: hidden;
        }
        .storefront-booking-overlay {
            position: fixed !important;
            inset: 0 !important;
            z-index: 99999 !important;
            background: #000 !important;
            color: #fff;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        body.storefront-booking-active {
            overflow: hidden !important;
        }
        body.storefront-booking-active .storefront-site-content {
            visibility: hidden !important;
            pointer-events: none !important;
        }

        /* Theme stylesheets are pre-compiled, so responsive corrections live here. */
        @supports (height: 100dvh) {
            .storefront-booking-overlay .min-h-screen { min-height: 100dvh; }
        }

        .sf-logo { max-width: 100%; }

        .sf-hero-title { line-height: 1.1; }

        @media (max-width: 1023px) {
            .sf-hero-title { font-size: clamp(2.25rem, 7.5vw, 3.5rem); }
        }

        @media (max-width: 639px) {
            .sf-hero-pill { padding: 0.625rem 1rem; }
            .sf-logo { min-width: 0; }
        }

        /* Opening-hours strings only fit on one line once the top bar is wide. */
        @media (max-width: 1023px) {
            .sf-hours { justify-content: center; }
            .sf-hours span { white-space: normal; text-align: center; }
        }

        /* Landscape phones: the hero must not reserve more height than the screen has. */
        @media (max-height: 520px) and (max-width: 1023px) {
            .sf-hero { min-height: 0; }
        }

        @media (min-width: 640px) and (max-width: 767px) {
            .sf-hero-perks { gap: 1.5rem; }
        }

        @media (max-width: 419px) {
            .sf-slot-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }

        .sf-booking-shell { min-height: 100dvh; display: flex; flex-direction: column; }
        .sf-booking-container { max-width: 1280px; margin: 0 auto; width: 100%; padding-left: 1rem; padding-right: 1rem; }
        .sf-booking-header { padding: 0.5rem 0 0.375rem; min-height: 4rem; display: flex; align-items: center; }
        .sf-booking-brand-name { font-family: Manrope, sans-serif; font-weight: 700; font-size: 1.125rem; line-height: 1.2; color: #fff; }
        .sf-booking-brand-tag { font-size: 0.625rem; letter-spacing: 0.12em; text-transform: uppercase; color: rgba(255,255,255,0.45); margin-top: 0.125rem; }
        .sf-booking-back-btn {
            display: inline-flex; align-items: center; gap: 0.25rem;
            font-size: 0.8125rem; font-weight: 500; color: rgba(255,255,255,0.8);
            padding: 0.375rem 0.5rem; margin-left: -0.5rem; border-radius: 0.5rem;
            transition: color 0.15s, background 0.15s;
        }
        .sf-booking-back-btn:hover { color: #fff; background: rgba(255,255,255,0.06); }
        .sf-booking-close-btn {
            font-size: 0.6875rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase;
            color: rgba(255,255,255,0.45); padding: 0.375rem 0.5rem; border-radius: 0.5rem;
        }
        .sf-booking-close-btn:hover { color: #fff; background: rgba(255,255,255,0.06); }

        /* Progress stepper */
        .sf-stepper {
            display: flex; align-items: center; gap: 0; overflow-x: auto;
            padding: 0.5rem 0 0.375rem; scrollbar-width: none; min-height: 3.5rem;
        }
        .sf-stepper::-webkit-scrollbar { display: none; }
        .sf-stepper-item {
            display: flex; align-items: center; gap: 0.375rem; flex-shrink: 0;
            color: rgba(255,255,255,0.35);
        }
        .sf-stepper-item.is-active { color: #fff; }
        .sf-stepper-item.is-done { color: rgba(255,255,255,0.85); }
        .sf-stepper-dot {
            width: 1.375rem; height: 1.375rem; border-radius: 9999px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.625rem; font-weight: 700; border: 1.5px solid rgba(255,255,255,0.2);
            background: transparent; flex-shrink: 0; color: rgba(255,255,255,0.35);
        }
        .sf-stepper-item.is-active .sf-stepper-dot {
            width: 1.5rem; height: 1.5rem;
            border-color: var(--color-primary, #e11d48); background: var(--color-primary, #e11d48); color: #fff;
            box-shadow: 0 0 0 3px rgba(225,29,72,0.2);
        }
        .sf-stepper-item.is-done .sf-stepper-dot {
            border-color: rgba(74, 222, 128, 0.55); background: rgba(74, 222, 128, 0.15); color: #86efac;
        }
        .sf-stepper-label { font-size: 0.6875rem; font-weight: 600; white-space: nowrap; }
        @media (max-width: 639px) { .sf-stepper-label { display: none; } .sf-stepper-item.is-active .sf-stepper-label { display: inline; } }
        .sf-stepper-line {
            flex: 1; min-width: 1rem; max-width: 2.5rem; height: 1.5px;
            background: rgba(255,255,255,0.15); margin: 0 0.375rem; flex-shrink: 0;
        }
        .sf-stepper-line.is-done { background: rgba(74, 222, 128, 0.45); }

        .sf-booking-main { flex: 1; width: 100%; padding: 1.25rem 0 5rem; }
        @media (min-width: 640px) { .sf-booking-main { padding-top: 1.5rem; } }
        @media (min-width: 1024px) { .sf-booking-main { padding-bottom: 2rem; } }
        .sf-booking-layout {
            display: grid; grid-template-columns: 1fr; gap: 1.25rem; align-items: start;
        }
        @media (min-width: 1024px) {
            .sf-booking-layout { grid-template-columns: minmax(0, 1.85fr) minmax(280px, 1fr); gap: 1.5rem; }
        }
        @media (min-width: 1280px) {
            .sf-booking-layout { grid-template-columns: minmax(0, 1.85fr) minmax(300px, 1fr); gap: 2rem; }
        }
        .sf-booking-primary { min-width: 0; }

        .sf-booking-card {
            border-radius: 1rem; border: 1px solid rgba(255,255,255,0.1);
            background: #1a1f2e; overflow: hidden; width: 100%;
        }
        .sf-booking-card-body { padding: 1rem 1.25rem; }
        @media (min-width: 640px) { .sf-booking-card-body { padding: 1.25rem 1.5rem; } }
        .sf-booking-section-title {
            font-family: Manrope, sans-serif; font-weight: 700; font-size: 1.125rem; color: #fff; margin-bottom: 0.375rem;
        }
        .sf-booking-section-sub {
            font-size: 0.8125rem; color: rgba(255,255,255,0.55); margin-bottom: 1rem; line-height: 1.5;
        }

        /* Stylist step */
        .sf-stylist-search-wrap {
            display: flex; align-items: center; gap: 0.625rem;
            border-radius: 0.75rem; border: 1px solid rgba(255,255,255,0.12);
            background: rgba(0,0,0,0.2); padding: 0.5625rem 0.875rem; margin-bottom: 0.875rem;
        }
        .sf-stylist-search-input {
            min-width: 0; flex: 1; background: transparent; border: 0; padding: 0;
            font-size: 0.8125rem; color: #fff; outline: none;
        }
        .sf-stylist-search-input::placeholder { color: rgba(255,255,255,0.4); }
        .sf-stylist-list { display: flex; flex-direction: column; gap: 0.5rem; }
        .sf-stylist-card {
            display: flex; align-items: center; gap: 0.75rem;
            width: 100%; min-height: 4.375rem; padding: 0.75rem 0.875rem;
            border-radius: 0.875rem; border: 1.5px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.04); text-align: left;
            transition: border-color 0.15s, background 0.15s, transform 0.15s;
        }
        .sf-stylist-card:hover {
            border-color: rgba(255,255,255,0.22); background: rgba(255,255,255,0.06);
        }
        .sf-stylist-card.is-selected {
            border-color: var(--color-primary, #e11d48);
            background: rgba(225,29,72,0.12);
            box-shadow: 0 0 0 1px rgba(225,29,72,0.25);
        }
        .sf-stylist-card--any .sf-stylist-card__icon {
            width: 2.5rem; height: 2.5rem; border-radius: 9999px; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            background: rgba(225,29,72,0.18); font-size: 1rem;
        }
        .sf-stylist-card__avatar {
            width: 2.5rem; height: 2.5rem; border-radius: 9999px; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            background: rgba(225,29,72,0.22); font-size: 0.75rem; font-weight: 700; color: #fff;
        }
        .sf-stylist-card__body { flex: 1; min-width: 0; }
        .sf-stylist-card__name {
            font-size: 0.875rem; font-weight: 700; color: #fff; line-height: 1.25;
        }
        .sf-stylist-card__meta {
            font-size: 0.6875rem; color: rgba(255,255,255,0.5); margin-top: 0.125rem; line-height: 1.35;
        }
        .sf-stylist-card__tick {
            width: 1.375rem; height: 1.375rem; border-radius: 9999px; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            border: 1.5px solid rgba(255,255,255,0.2); font-size: 0.6875rem; font-weight: 700;
            color: transparent; transition: border-color 0.15s, background 0.15s, color 0.15s;
        }
        .sf-stylist-card.is-selected .sf-stylist-card__tick {
            border-color: var(--color-primary, #e11d48);
            background: var(--color-primary, #e11d48);
            color: #fff;
        }

        /* Date & time step */
        .sf-datetime-step { display: flex; flex-direction: column; gap: 1rem; }
        .sf-datetime-step__head { margin-bottom: 0.125rem; }
        .sf-datetime-step__stylist {
            margin-top: 0.5rem; font-size: 0.8125rem; color: rgba(255,255,255,0.55);
        }
        .sf-datetime-step__stylist span { color: #fff; font-weight: 600; }
        .sf-datetime-date {
            display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;
            width: 100%; padding: 0.875rem 1rem; text-align: left;
            border-radius: 0.875rem; border: 1px solid rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.06);
            transition: border-color 0.15s, background 0.15s;
        }
        .sf-datetime-date:hover { border-color: rgba(255,255,255,0.22); background: rgba(255,255,255,0.08); }
        .sf-datetime-date__label {
            font-size: 0.625rem; font-weight: 700; letter-spacing: 0.12em;
            text-transform: uppercase; color: rgba(255,255,255,0.4);
        }
        .sf-datetime-date__value {
            margin-top: 0.25rem; font-family: Manrope, sans-serif;
            font-size: 0.9375rem; font-weight: 700; color: #fff; line-height: 1.3;
        }
        .sf-datetime-date__meta {
            margin-top: 0.25rem; font-size: 0.6875rem; color: rgba(255,255,255,0.45);
        }
        .sf-datetime-date__icon {
            width: 2.25rem; height: 2.25rem; border-radius: 0.625rem; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.7);
        }
        .sf-datetime-calendar {
            margin-top: 0.5rem; border-radius: 1rem; border: 1px solid rgba(255,255,255,0.1);
            background: #12151f; padding: 1rem 1.25rem;
            box-shadow: 0 16px 40px rgba(0,0,0,0.45); z-index: 20; position: relative;
        }
        .sf-datetime-slots {
            border-radius: 0.875rem; border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.03); padding: 1rem 1.125rem;
        }
        .sf-datetime-slots__head {
            display: flex; align-items: flex-start; justify-content: space-between;
            gap: 0.75rem; margin-bottom: 0.875rem;
        }
        .sf-datetime-slots__title {
            font-family: Manrope, sans-serif; font-size: 0.875rem; font-weight: 700; color: #fff;
        }
        .sf-datetime-slots__count {
            margin-top: 0.125rem; font-size: 0.6875rem; color: rgba(255,255,255,0.42);
        }
        .sf-datetime-slots__refresh {
            display: inline-flex; align-items: center; gap: 0.25rem;
            font-size: 0.6875rem; font-weight: 600; color: var(--color-primary, #e11d48);
            padding: 0.25rem 0.5rem; border-radius: 0.375rem;
            border: none; background: transparent; cursor: pointer;
        }
        .sf-datetime-slots__refresh:hover { background: rgba(225,29,72,0.1); }
        .sf-datetime-slots__refresh:disabled { opacity: 0.4; cursor: not-allowed; }
        .sf-datetime-slots__note {
            font-size: 0.6875rem; color: rgba(255,255,255,0.5);
            margin-bottom: 0.75rem; padding: 0.5rem 0.625rem;
            border-radius: 0.5rem; background: rgba(255,255,255,0.04);
        }
        .sf-datetime-slots__error {
            font-size: 0.75rem; color: #fca5a5;
            margin-bottom: 0.75rem; padding: 0.5rem 0.625rem;
            border-radius: 0.5rem; background: rgba(239,68,68,0.15);
            border: 1px solid rgba(239,68,68,0.3);
        }
        .sf-datetime-slots__loading,
        .sf-datetime-slots__empty {
            text-align: center; padding: 1.25rem 0.5rem;
            font-size: 0.8125rem; color: rgba(255,255,255,0.5);
        }
        .sf-datetime-slots__back {
            margin-top: 0.75rem; display: inline-flex; align-items: center; justify-content: center;
            padding: 0.5rem 1rem; border-radius: 9999px;
            border: 1px solid rgba(225,29,72,0.35); background: rgba(225,29,72,0.1);
            font-size: 0.75rem; font-weight: 600; color: var(--color-primary, #e11d48);
        }
        .sf-datetime-period { margin-bottom: 0.875rem; }
        .sf-datetime-period:last-child { margin-bottom: 0; }
        .sf-datetime-period__label {
            font-size: 0.625rem; font-weight: 700; letter-spacing: 0.14em;
            text-transform: uppercase; color: rgba(255,255,255,0.38);
            margin-bottom: 0.5rem;
        }
        .sf-slot-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.375rem;
        }
        @media (min-width: 640px) {
            .sf-slot-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); }
        }
        @media (min-width: 1024px) {
            .sf-slot-grid { grid-template-columns: repeat(6, minmax(0, 1fr)); }
        }
        .sf-slot-btn {
            display: inline-flex; align-items: center; justify-content: center;
            min-height: 2rem; padding: 0.375rem 0.25rem;
            border-radius: 0.5rem; border: 1px solid rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.04);
            font-size: 0.75rem; font-weight: 600; color: rgba(255,255,255,0.88);
            transition: border-color 0.12s, background 0.12s, transform 0.12s, box-shadow 0.12s;
            cursor: pointer;
        }
        .sf-slot-btn:hover:not(:disabled) {
            border-color: rgba(225,29,72,0.4); background: rgba(225,29,72,0.1);
        }
        .sf-slot-btn:disabled {
            opacity: 0.3; cursor: not-allowed; border-color: rgba(255,255,255,0.05);
        }
        .sf-slot-btn.is-selected {
            border-color: var(--color-primary, #e11d48);
            background: var(--color-primary, #e11d48);
            color: #fff;
            box-shadow: 0 4px 14px rgba(225,29,72,0.35);
        }
        .sf-slot-btn.is-selected .sf-slot-btn__text::before {
            content: '✓ ';
            font-size: 0.6875rem;
        }

        .sf-search-wrap {
            display: flex; align-items: center; gap: 0.625rem;
            border-radius: 0.75rem; border: 1px solid rgba(255,255,255,0.15);
            background: rgba(0,0,0,0.25); padding: 0.625rem 0.875rem;
        }
        .sf-search-wrap:focus-within { border-color: rgba(var(--color-primary-rgb, 225,29,72), 0.5); box-shadow: 0 0 0 2px rgba(225,29,72,0.15); }
        .sf-search-input {
            min-width: 0; flex: 1; background: transparent; border: 0; padding: 0;
            font-size: 0.875rem; color: #fff; outline: none;
        }
        .sf-search-input::placeholder { color: rgba(255,255,255,0.4); }
        .sf-popular-row { display: flex; flex-wrap: wrap; align-items: center; gap: 0.375rem; margin-top: 0.625rem; }
        .sf-popular-label { font-size: 0.6875rem; color: rgba(255,255,255,0.4); margin-right: 0.25rem; }
        .sf-popular-chip {
            font-size: 0.6875rem; font-weight: 600; padding: 0.25rem 0.625rem; border-radius: 9999px;
            border: 1px solid rgba(255,255,255,0.12); background: rgba(255,255,255,0.04);
            color: rgba(255,255,255,0.75); transition: border-color 0.15s, background 0.15s;
        }
        .sf-popular-chip:hover { border-color: rgba(255,255,255,0.25); background: rgba(255,255,255,0.08); color: #fff; }

        .sf-services-scroll {
            overflow-y: auto; -webkit-overflow-scrolling: touch;
            max-height: min(56dvh, 28rem); margin-top: 0.875rem;
        }
        @media (min-width: 1024px) { .sf-services-scroll { max-height: min(62dvh, 32rem); } }

        .sf-cat-block { border-radius: 0.625rem; border: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.02); overflow: hidden; }
        .sf-cat-toggle {
            width: 100%; display: flex; align-items: center; gap: 0.625rem;
            padding: 0.625rem 0.75rem; text-align: left;
            transition: background 0.15s;
        }
        .sf-cat-toggle:hover { background: rgba(255,255,255,0.04); }
        .sf-cat-icon { font-size: 0.875rem; opacity: 0.7; flex-shrink: 0; width: 1.25rem; text-align: center; }
        .sf-cat-title { font-size: 0.875rem; font-weight: 600; color: #fff; line-height: 1.2; }
        .sf-cat-sub { font-size: 0.6875rem; color: rgba(255,255,255,0.42); margin-top: 0.0625rem; }
        .sf-cat-count { font-size: 0.75rem; font-weight: 600; color: rgba(255,255,255,0.45); font-variant-numeric: tabular-nums; flex-shrink: 0; }
        .sf-cat-chevron { width: 1rem; height: 1rem; color: rgba(255,255,255,0.45); flex-shrink: 0; transition: transform 0.2s; }
        .sf-cat-chevron.is-open { transform: rotate(180deg); }

        .sf-svc-row {
            display: flex; align-items: center; gap: 0.625rem;
            padding: 0.625rem 0.75rem; cursor: pointer; border-top: 1px solid rgba(255,255,255,0.06);
            transition: background 0.15s;
        }
        .sf-svc-row:hover { background: rgba(255,255,255,0.04); }
        .sf-svc-row.is-selected {
            background: rgba(225,29,72,0.1);
            box-shadow: inset 3px 0 0 var(--color-primary, #e11d48);
            outline: 1px solid rgba(225,29,72,0.22);
            outline-offset: -1px;
        }
        .sf-svc-check {
            width: 1.125rem; height: 1.125rem; border-radius: 0.25rem; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            border: 1.5px solid rgba(255,255,255,0.25); font-size: 0.625rem; color: transparent;
        }
        .sf-svc-row.is-selected .sf-svc-check {
            border-color: var(--color-primary, #e11d48); background: var(--color-primary, #e11d48); color: #fff;
        }
        .sf-svc-name { font-size: 0.8125rem; font-weight: 600; color: #fff; line-height: 1.25; }
        .sf-svc-meta { font-size: 0.6875rem; color: rgba(255,255,255,0.45); margin-top: 0.0625rem; }
        .sf-svc-price { font-size: 0.8125rem; font-weight: 600; color: #fff; flex-shrink: 0; margin-left: auto; }

        /* Summary sidebar — desktop only */
        .sf-booking-summary { display: none; }
        @media (min-width: 1024px) {
            .sf-booking-summary {
                display: block;
                position: sticky;
                top: 6.25rem;
                align-self: start;
                max-height: calc(100dvh - 7rem);
                overflow-y: auto;
            }
        }
        .sf-booking-summary-inner {
            border-radius: 1rem; border: 1px solid rgba(255,255,255,0.1);
            background: #141824; padding: 1.25rem;
        }
        .sf-booking-summary-title {
            font-family: Manrope, sans-serif; font-size: 0.6875rem; font-weight: 700;
            letter-spacing: 0.14em; text-transform: uppercase; color: rgba(255,255,255,0.45); margin-bottom: 1rem;
        }
        .sf-booking-summary-empty { font-size: 0.8125rem; color: rgba(255,255,255,0.4); line-height: 1.5; }
        .sf-booking-summary-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 0.625rem; }
        .sf-booking-summary-item.is-package .sf-booking-summary-item-head {
            padding: 0.75rem;
            border-radius: 0.625rem;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
        }
        .sf-booking-summary-item { display: flex; flex-direction: column; gap: 0.5rem; }
        .sf-booking-summary-item-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; }
        .sf-booking-summary-duration {
            margin-top: 0.625rem; font-size: 0.6875rem; color: rgba(255,255,255,0.42);
        }
        .sf-booking-summary-package-toggle {
            display: inline-flex; align-items: center; gap: 0.375rem;
            padding: 0; border: none; background: none;
            font-size: 0.6875rem; font-weight: 600; color: rgba(255,255,255,0.55);
            cursor: pointer;
        }
        .sf-booking-summary-package-toggle:hover { color: #fff; }
        .sf-booking-summary-package-list {
            list-style: none; margin: 0.375rem 0 0; padding: 0.5rem 0 0;
            border-top: 1px solid rgba(255,255,255,0.06);
            display: flex; flex-direction: column; gap: 0.3125rem;
        }
        .sf-booking-summary-package-line {
            display: flex; justify-content: space-between; gap: 0.5rem;
            font-size: 0.6875rem; color: rgba(255,255,255,0.65);
        }
        .sf-booking-summary-item-main { display: flex; gap: 0.5rem; min-width: 0; }
        .sf-booking-summary-check {
            width: 1rem; height: 1rem; border-radius: 9999px; flex-shrink: 0; margin-top: 0.125rem;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.5rem; font-weight: 700; background: rgba(225,29,72,0.2); color: var(--color-primary, #e11d48);
        }
        .sf-booking-summary-item-name { font-size: 0.8125rem; font-weight: 600; color: #fff; line-height: 1.3; }
        .sf-booking-summary-item-meta { font-size: 0.6875rem; color: rgba(255,255,255,0.45); margin-top: 0.0625rem; }
        .sf-booking-summary-item-price { font-size: 0.8125rem; font-weight: 600; color: #fff; flex-shrink: 0; }
        .sf-booking-summary-extra { margin-top: 0; padding-top: 0; border-top: none; }
        .sf-booking-summary-details {
            margin-top: 1rem; padding-top: 1rem;
            border-top: 1px solid rgba(255,255,255,0.08);
            display: flex; flex-direction: column; gap: 0.625rem;
        }
        .sf-booking-summary-extra-label { font-size: 0.625rem; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.38); }
        .sf-booking-summary-extra-value { font-size: 0.875rem; font-weight: 600; color: rgba(255,255,255,0.85); margin-top: 0.125rem; }
        .sf-booking-summary-extra-value.is-highlight { color: #fff; font-weight: 700; }
        .sf-booking-summary-total {
            display: flex; justify-content: space-between; align-items: center;
            margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.12);
            font-size: 0.875rem; color: rgba(255,255,255,0.55);
        }
        .sf-booking-summary-total span:last-child { font-size: 1.125rem; color: #fff; }
        .sf-booking-summary-cta {
            width: 100%; margin-top: 1rem; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            padding: 0.75rem 1.25rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 700;
            background: var(--color-primary, #e11d48); color: #fff;
            box-shadow: 0 4px 14px rgba(225,29,72,0.35); transition: filter 0.15s;
        }
        .sf-booking-summary-cta:hover { filter: brightness(1.08); }
        .sf-booking-summary-cta:disabled,
        .sf-booking-summary-cta.is-disabled {
            opacity: 0.45; cursor: not-allowed; filter: none;
            box-shadow: none;
        }
        .sf-booking-summary-cta.is-ready {
            opacity: 1;
            box-shadow: 0 6px 20px rgba(225,29,72,0.45);
        }
        .sf-booking-summary-hint { font-size: 0.75rem; color: rgba(255,255,255,0.4); margin-top: 0.75rem; text-align: center; }

        /* Mobile sticky bar */
        .sf-booking-mobile-bar {
            position: fixed; left: 0; right: 0; bottom: 0; z-index: 60;
            background: rgba(0,0,0,0.92); backdrop-filter: blur(12px);
            border-top: 1px solid rgba(255,255,255,0.1);
            padding: 0.75rem 1rem calc(0.75rem + env(safe-area-inset-bottom));
        }
        .sf-booking-mobile-bar-inner { max-width: 1280px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; }
        .sf-booking-mobile-cta {
            display: inline-flex; align-items: center; gap: 0.375rem; flex-shrink: 0;
            padding: 0.625rem 1.125rem; border-radius: 9999px; font-size: 0.8125rem; font-weight: 700;
            background: var(--color-primary, #e11d48); color: #fff;
        }
        .sf-booking-mobile-cta:disabled,
        .sf-booking-mobile-cta.is-disabled {
            opacity: 0.45; cursor: not-allowed;
        }
        .sf-booking-mobile-cta.is-ready {
            box-shadow: 0 4px 16px rgba(225,29,72,0.4);
        }

        /* Keeps the mobile section menu reachable when the nav is pinned on short screens. */
        .sf-sticky-nav {
            max-height: 100dvh;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        /* ── Home page Services section ─────────────────────────────────── */
        .sf-home-services {
            width: 100%;
            padding: 4.5rem 1.5rem 5rem;
            background:
                radial-gradient(circle at 85% 20%, rgba(220, 75, 65, 0.06), transparent 30%),
                var(--color-testimonial-bg, #fff8f7);
            overflow: hidden;
        }
        .sf-home-services__inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0;
        }
        .sf-home-services__header {
            max-width: 42rem;
            margin: 0 auto 2.5rem;
            text-align: center;
        }
        .sf-home-services__eyebrow {
            display: block;
            font-family: Manrope, sans-serif;
            font-size: 0.8125rem;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--color-primary, #e11d48);
            margin-bottom: 0.5rem;
        }
        .sf-home-services__title {
            font-family: Manrope, sans-serif;
            font-size: clamp(1.75rem, 4vw, 2.75rem);
            font-weight: 800;
            line-height: 1.15;
            color: #111;
            letter-spacing: -0.02em;
        }
        .sf-home-services__subtitle {
            margin-top: 0.75rem;
            font-family: Inter, sans-serif;
            font-size: 0.9375rem;
            line-height: 1.6;
            color: rgba(0, 0, 0, 0.55);
        }
        .sf-home-services__cats-wrap {
            position: relative;
            margin-bottom: 2rem;
            padding: 0 3rem;
        }
        .sf-home-services__cats {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            overflow-x: auto;
            scroll-behavior: smooth;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
            scroll-padding-inline: 0.5rem;
            padding: 0.25rem 1rem 0.5rem 0.125rem;
        }
        .sf-home-services__cats::-webkit-scrollbar { display: none; }
        .sf-home-services__cats.is-grabbing { cursor: grabbing; scroll-snap-type: none; }
        .sf-home-services__cat-btn {
            flex-shrink: 0;
            padding: 0.5625rem 1.125rem;
            border-radius: 9999px;
            border: 1px solid transparent;
            font-family: Manrope, sans-serif;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            white-space: nowrap;
            cursor: pointer;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s, border-color 0.2s;
            background: rgba(0, 0, 0, 0.06);
            color: rgba(0, 0, 0, 0.65);
        }
        .sf-home-services__cat-btn:hover { background: rgba(0, 0, 0, 0.1); }
        .sf-home-services__cat-btn.is-active {
            background: var(--color-primary, #e11d48);
            color: #fff;
            box-shadow: 0 4px 14px rgba(220, 75, 65, 0.25);
        }
        .sf-home-services__cat-nav {
            display: none;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 3;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 9999px;
            border: 1px solid #eadedc;
            background: #fff;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
            font-size: 1rem;
            line-height: 1;
            color: rgba(0, 0, 0, 0.65);
            cursor: pointer;
            align-items: center;
            justify-content: center;
            transition: color 0.15s, border-color 0.15s, box-shadow 0.15s;
        }
        .sf-home-services__cat-nav:hover {
            color: #111;
            border-color: rgba(217, 75, 64, 0.35);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }
        .sf-home-services__cat-nav:disabled {
            opacity: 0.35;
            cursor: default;
            pointer-events: none;
        }
        .sf-home-services__cat-nav--prev { left: 0; }
        .sf-home-services__cat-nav--next { right: 0; }
        @media (min-width: 768px) {
            .sf-home-services__cat-nav { display: inline-flex; }
        }
        @media (max-width: 767px) {
            .sf-home-services__cats-wrap { padding: 0; }
        }
        .sf-home-services__cats-wrap::before,
        .sf-home-services__cats-wrap::after {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0.5rem;
            width: 2.75rem;
            z-index: 2;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .sf-home-services__cats-wrap::before {
            left: 3rem;
            background: linear-gradient(to right, var(--color-testimonial-bg, #fff8f7), transparent);
        }
        .sf-home-services__cats-wrap::after {
            right: 3rem;
            background: linear-gradient(to left, var(--color-testimonial-bg, #fff8f7), transparent);
        }
        .sf-home-services__cats-wrap.has-scroll-left::before { opacity: 1; }
        .sf-home-services__cats-wrap.has-scroll-right::after { opacity: 1; }
        @media (max-width: 767px) {
            .sf-home-services__cats-wrap::before { left: 0; }
            .sf-home-services__cats-wrap::after { right: 0; }
        }
        .sf-home-services__grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        .sf-home-services__grid.is-single {
            max-width: 700px;
            margin: 0 auto;
        }
        @media (min-width: 768px) {
            .sf-home-services__grid.is-multi {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1.25rem;
            }
        }
        .sf-home-services__card {
            display: flex;
            flex-direction: column;
            gap: 0.875rem;
            width: 100%;
            padding: 1.25rem 1.375rem;
            text-align: left;
            border-radius: 1rem;
            border: 1px solid #eadedc;
            background: #fff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s, background 0.2s;
        }
        .sf-home-services__card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
            border-color: rgba(220, 75, 65, 0.25);
        }
        .sf-home-services__card.is-selected {
            border-color: var(--color-primary, #e11d48);
            background: rgba(220, 75, 65, 0.04);
            box-shadow: 0 12px 30px rgba(220, 75, 65, 0.12);
        }
        .sf-home-services__card-top {
            display: flex;
            align-items: flex-start;
            gap: 0.875rem;
        }
        .sf-home-services__icon {
            width: 3rem;
            height: 3rem;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.75rem;
            background: rgba(220, 75, 65, 0.1);
        }
        .sf-home-services__icon img {
            width: 1.625rem;
            height: 1.625rem;
            object-fit: contain;
        }
        .sf-home-services__card-body { flex: 1; min-width: 0; }
        .sf-home-services__card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
        }
        .sf-home-services__name {
            font-family: Manrope, sans-serif;
            font-size: 1.0625rem;
            font-weight: 700;
            color: #111;
            line-height: 1.3;
        }
        .sf-home-services__price {
            font-family: Manrope, sans-serif;
            font-size: 1.25rem;
            font-weight: 800;
            color: #111;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .sf-home-services__meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.375rem;
            margin-top: 0.5rem;
        }
        .sf-home-services__pill {
            display: inline-flex;
            align-items: center;
            padding: 0.1875rem 0.5625rem;
            border-radius: 9999px;
            font-family: Manrope, sans-serif;
            font-size: 0.6875rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            background: rgba(0, 0, 0, 0.05);
            color: rgba(0, 0, 0, 0.55);
        }
        .sf-home-services__dot {
            font-size: 0.625rem;
            color: rgba(0, 0, 0, 0.35);
        }
        .sf-home-services__desc {
            margin-top: 0.625rem;
            font-family: Inter, sans-serif;
            font-size: 0.8125rem;
            line-height: 1.55;
            color: rgba(0, 0, 0, 0.55);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .sf-home-services__select {
            margin-top: auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            width: 100%;
            padding: 0.625rem 1rem;
            border-radius: 0.625rem;
            border: 1px solid #eadedc;
            background: #fff;
            font-family: Manrope, sans-serif;
            font-size: 0.8125rem;
            font-weight: 600;
            color: rgba(0, 0, 0, 0.65);
            transition: background 0.15s, border-color 0.15s, color 0.15s;
        }
        .sf-home-services__card:hover .sf-home-services__select {
            border-color: rgba(220, 75, 65, 0.3);
            color: var(--color-primary, #e11d48);
        }
        .sf-home-services__card.is-selected .sf-home-services__select {
            background: var(--color-primary, #e11d48);
            border-color: var(--color-primary, #e11d48);
            color: #fff;
        }
        .sf-home-services__empty {
            text-align: center;
            padding: 2rem 1rem;
            font-family: Inter, sans-serif;
            font-size: 0.875rem;
            color: rgba(0, 0, 0, 0.5);
        }
        .sf-home-services__cta {
            margin-top: 2.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }
        .sf-home-services__cta-summary {
            font-family: Inter, sans-serif;
            font-size: 0.875rem;
            color: rgba(0, 0, 0, 0.55);
            text-align: center;
        }
        .sf-home-services__cta-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 2rem;
            border-radius: 9999px;
            border: none;
            background: var(--color-primary, #e11d48);
            color: #fff;
            font-family: Manrope, sans-serif;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.15s, filter 0.15s;
            box-shadow: 0 4px 16px rgba(220, 75, 65, 0.28);
        }
        .sf-home-services__cta-btn:hover { filter: brightness(1.06); transform: scale(1.02); }
        .sf-home-services__cta-btn:disabled { opacity: 0.55; cursor: not-allowed; transform: none; }
        .sf-home-services__sticky {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 40;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            border-top: 1px solid #eadedc;
            box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.06);
            padding: 0.875rem 1rem calc(0.875rem + env(safe-area-inset-bottom));
        }
        .sf-home-services__sticky-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .sf-home-services__sticky-summary { flex: 1; min-width: 0; }
        .sf-home-services__sticky-price {
            font-family: Manrope, sans-serif;
            font-size: 1.125rem;
            font-weight: 800;
            color: #111;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .sf-home-services__sticky-btn {
            padding: 0.625rem 1.25rem !important;
            font-size: 0.8125rem !important;
            flex-shrink: 0;
        }
        .sf-home-services__sticky-text {
            min-width: 0;
            font-family: Manrope, sans-serif;
            font-size: 0.875rem;
            font-weight: 600;
            color: #111;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sf-home-services__sticky-meta {
            font-family: Inter, sans-serif;
            font-size: 0.75rem;
            color: rgba(0, 0, 0, 0.5);
            margin-top: 0.125rem;
        }
        @media (max-width: 639px) {
            .sf-home-services__sticky-inner {
                gap: 0.625rem;
            }
            .sf-home-services__sticky-price {
                font-size: 1rem;
            }
            .sf-home-services__sticky-btn {
                padding: 0.5625rem 1rem !important;
            }
        }
        .sf-home-services.has-sticky-selection { padding-bottom: 5.5rem; }

        /* ── Home page Packages section ─────────────────────────────────── */
        .sf-home-packages {
            width: 100%;
            padding: 4.5rem 1.5rem 5.625rem;
            background: #fff9f8;
            overflow: hidden;
        }
        .sf-home-packages__inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0;
        }
        .sf-home-packages__header {
            max-width: 42rem;
            margin: 0 auto 2rem;
            text-align: center;
        }
        .sf-home-packages__eyebrow {
            display: block;
            font-family: Manrope, sans-serif;
            font-size: 0.8125rem;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--color-primary, #d94b40);
            margin-bottom: 0.5rem;
        }
        .sf-home-packages__title {
            font-family: Manrope, sans-serif;
            font-size: clamp(1.75rem, 4vw, 2.75rem);
            font-weight: 800;
            line-height: 1.15;
            color: #111;
            letter-spacing: -0.02em;
        }
        .sf-home-packages__subtitle {
            margin-top: 0.75rem;
            font-family: Inter, sans-serif;
            font-size: 0.9375rem;
            line-height: 1.6;
            color: #777;
        }
        .sf-home-packages__grid {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: 1fr;
            width: 100%;
        }
        .sf-home-packages__grid.is-single {
            display: flex;
            justify-content: center;
        }
        .sf-home-packages__grid.is-single .sf-home-packages__card {
            width: 100%;
            max-width: 520px;
        }
        @media (min-width: 768px) {
            .sf-home-packages__grid.is-multi-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .sf-home-packages__grid.is-multi-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        @media (min-width: 640px) and (max-width: 767px) {
            .sf-home-packages__grid.is-multi-2,
            .sf-home-packages__grid.is-multi-3 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 639px) {
            .sf-home-packages__grid.is-scroll {
                display: flex;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                scrollbar-width: none;
                gap: 1rem;
                padding-bottom: 0.5rem;
                -webkit-overflow-scrolling: touch;
            }
            .sf-home-packages__grid.is-scroll::-webkit-scrollbar { display: none; }
            .sf-home-packages__grid.is-scroll .sf-home-packages__card {
                flex: 0 0 min(88vw, 340px);
                scroll-snap-align: start;
            }
        }
        @media (min-width: 640px) {
            .sf-home-packages__grid.is-scroll {
                display: grid;
                overflow: visible;
                padding-bottom: 0;
            }
            .sf-home-packages__grid.is-scroll .sf-home-packages__card {
                flex: unset;
                scroll-snap-align: unset;
            }
        }
        .sf-home-packages__card {
            display: flex;
            flex-direction: column;
            border-radius: 1.125rem;
            border: 1px solid #eee4e2;
            background: #fff;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            cursor: pointer;
            text-align: left;
            transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
        }
        .sf-home-packages__card:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 36px rgba(0, 0, 0, 0.08);
            border-color: rgba(217, 75, 64, 0.35);
        }
        .sf-home-packages__card.is-selected {
            border: 2px solid var(--color-primary, #d94b40);
            background: #fff5f3;
            box-shadow: 0 14px 36px rgba(217, 75, 64, 0.14);
        }
        .sf-home-packages__image-wrap {
            position: relative;
            height: 200px;
            overflow: hidden;
            background: #f5f0ef;
        }
        .sf-home-packages__image-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.35s ease;
        }
        .sf-home-packages__card:hover .sf-home-packages__image-wrap img {
            transform: scale(1.04);
        }
        .sf-home-packages__badge {
            position: absolute;
            padding: 0.3125rem 0.625rem;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.94);
            font-family: Manrope, sans-serif;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: var(--color-primary, #d94b40);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .sf-home-packages__badge--featured {
            top: 0.875rem;
            left: 0.875rem;
        }
        .sf-home-packages__badge--save {
            top: 0.875rem;
            right: 0.875rem;
            background: var(--color-primary, #d94b40);
            color: #fff;
        }
        .sf-home-packages__body {
            display: flex;
            flex-direction: column;
            flex: 1;
            padding: 1rem 1.125rem 1.125rem;
        }
        .sf-home-packages__name {
            font-family: Manrope, sans-serif;
            font-size: 1.125rem;
            font-weight: 700;
            color: #111;
            line-height: 1.25;
            margin-bottom: 0.375rem;
        }
        .sf-home-packages__desc {
            font-family: Inter, sans-serif;
            font-size: 0.8125rem;
            line-height: 1.5;
            color: #777;
            margin-bottom: 0.5rem;
        }
        .sf-home-packages__meta {
            font-family: Inter, sans-serif;
            font-size: 0.75rem;
            font-weight: 600;
            color: #555;
            margin-bottom: 0.75rem;
        }
        .sf-home-packages__items {
            list-style: none;
            margin: 0;
            padding: 0;
            flex: 1;
        }
        .sf-home-packages__item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.5rem;
            padding: 0.3125rem 0;
            font-family: Inter, sans-serif;
            font-size: 0.75rem;
            color: #444;
        }
        .sf-home-packages__item-name {
            display: flex;
            align-items: flex-start;
            gap: 0.375rem;
            min-width: 0;
        }
        .sf-home-packages__item-name::before {
            content: '✓';
            color: var(--color-primary, #d94b40);
            font-weight: 700;
            flex-shrink: 0;
        }
        .sf-home-packages__item-price {
            font-weight: 600;
            color: #111;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .sf-home-packages__count {
            margin-top: 0.5rem;
            font-family: Inter, sans-serif;
            font-size: 0.6875rem;
            color: #777;
        }
        .sf-home-packages__more {
            margin-top: 0.375rem;
            padding: 0;
            border: none;
            background: none;
            font-family: Inter, sans-serif;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--color-primary, #d94b40);
            cursor: pointer;
            text-align: left;
        }
        .sf-home-packages__more:hover { text-decoration: underline; }
        .sf-home-packages__pricing {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #f0e8e6;
        }
        .sf-home-packages__value-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
            font-family: Inter, sans-serif;
            font-size: 0.75rem;
            color: #777;
            margin-bottom: 0.25rem;
        }
        .sf-home-packages__value-row.is-struck span:last-child {
            text-decoration: line-through;
        }
        .sf-home-packages__value-row .sf-home-packages__price {
            font-family: Manrope, sans-serif;
            font-size: 1.25rem;
            font-weight: 800;
            color: #111;
            line-height: 1;
        }
        .sf-home-packages__save-row {
            margin-top: 0.5rem;
            text-align: center;
        }
        .sf-home-packages__save {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            background: rgba(217, 75, 64, 0.1);
            font-family: Manrope, sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: var(--color-primary, #d94b40);
        }
        .sf-home-packages__select {
            margin-top: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.5625rem 0.875rem;
            border-radius: 0.625rem;
            border: 1px solid #eee4e2;
            background: #fff;
            font-family: Manrope, sans-serif;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #555;
            transition: background 0.15s, border-color 0.15s, color 0.15s;
            pointer-events: none;
        }
        .sf-home-packages__card:hover .sf-home-packages__select {
            border-color: rgba(217, 75, 64, 0.35);
            color: var(--color-primary, #d94b40);
        }
        .sf-home-packages__card.is-selected .sf-home-packages__select {
            background: var(--color-primary, #d94b40);
            border-color: var(--color-primary, #d94b40);
            color: #fff;
        }
        .sf-home-packages__cta {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }
        .sf-home-packages__cta-summary {
            font-family: Inter, sans-serif;
            font-size: 0.875rem;
            color: #777;
            text-align: center;
        }
        .sf-home-packages.has-sticky-selection { padding-bottom: 5.5rem; }
    </style>
    @stack('head')
</head>
<body class="antialiased bg-white text-black">
    <div class="storefront-site-content">
        @yield('content')
    </div>
    @isset($salon)
        @include('storefront.partials.booking-flow')
    @endisset

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        window.__STOREFRONT_BOOKING_ENABLED__ = @json((bool) ($data['salon']['online_booking_enabled'] ?? $salon->online_booking_enabled ?? false));
        (function () {
            function syncBookingHash() {
                var open = window.location.hash === '#book' || window.location.hash.indexOf('#book') === 0;
                document.body.classList.toggle('storefront-booking-active', open);
                window.dispatchEvent(new CustomEvent('storefront-booking-toggle', { detail: { open: open } }));
            }
            if (window.location.hash === '#book' || window.location.hash.indexOf('#book') === 0) {
                document.body.classList.add('storefront-booking-active');
            }
            window.addEventListener('hashchange', syncBookingHash);
            document.addEventListener('DOMContentLoaded', syncBookingHash);
            syncBookingHash();
            window.storefrontOpenBooking = function (opts) {
                opts = opts || {};
                window.dispatchEvent(new CustomEvent('storefront-book-preselect', { detail: opts }));
                if (window.location.hash !== '#book') {
                    window.location.hash = 'book';
                } else {
                    window.dispatchEvent(new CustomEvent('storefront-booking-toggle', { detail: { open: true } }));
                }
            };
            window.storefrontHomeCart = (function () {
                var serviceIds = {};
                var packageIds = {};
                function snapshot() {
                    return {
                        serviceIds: Object.keys(serviceIds).map(Number),
                        packageIds: Object.keys(packageIds).map(Number),
                    };
                }
                function emit() {
                    var snap = snapshot();
                    window.dispatchEvent(new CustomEvent('storefront-home-cart-change', { detail: snap }));
                    document.querySelectorAll('[data-home-package]').forEach(function (el) {
                        var on = !!packageIds[String(el.getAttribute('data-home-package'))];
                        el.classList.toggle('is-selected', on);
                        var label = el.querySelector('[data-home-package-label]');
                        if (label) {
                            label.textContent = on ? '✓ Package selected' : 'Select package →';
                        }
                    });
                }
                return {
                    toggleService: function (id) {
                        id = String(id);
                        if (serviceIds[id]) {
                            delete serviceIds[id];
                        } else {
                            serviceIds[id] = true;
                            packageIds = {};
                        }
                        emit();
                    },
                    togglePackage: function (id) {
                        id = String(id);
                        if (packageIds[id]) {
                            delete packageIds[id];
                        } else {
                            packageIds = {};
                            packageIds[id] = true;
                            serviceIds = {};
                        }
                        emit();
                    },
                    hasService: function (id) { return !!serviceIds[String(id)]; },
                    snapshot: snapshot,
                    bookSelected: function () {
                        var s = snapshot();
                        if (s.serviceIds.length || s.packageIds.length) {
                            window.storefrontOpenBooking(s);
                            return;
                        }
                        window.location.hash = 'book';
                    },
                };
            })();
        })();

        // The section nav switches to fixed positioning on scroll; a spacer keeps the
        // page from jumping by the nav height when that happens.
        (function () {
            function initNavSpacer() {
                var nav = document.querySelector('.sf-sticky-nav');
                if (!nav || !nav.parentNode || nav.hasAttribute('data-overlay-nav')) return;

                var spacer = document.createElement('div');
                spacer.setAttribute('aria-hidden', 'true');
                spacer.className = 'sf-sticky-nav-spacer';
                spacer.style.display = 'none';
                nav.parentNode.insertBefore(spacer, nav.nextSibling);

                var flowHeight = nav.offsetHeight;

                function spacerFill() {
                    var bg = window.getComputedStyle(nav).backgroundColor;
                    var m = bg && bg.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
                    if (m && (+m[1] + +m[2] + +m[3]) < 90) {
                        spacer.style.backgroundColor = 'rgb(' + m[1] + ',' + m[2] + ',' + m[3] + ')';
                    } else {
                        spacer.style.backgroundColor = bg || 'transparent';
                    }
                }

                function sync() {
                    if (window.getComputedStyle(nav).position === 'fixed') {
                        spacer.style.height = flowHeight + 'px';
                        spacer.style.display = 'block';
                        spacerFill();
                    } else {
                        flowHeight = nav.offsetHeight;
                        spacer.style.display = 'none';
                    }
                }

                window.addEventListener('scroll', sync, { passive: true });
                window.addEventListener('resize', sync);
                sync();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initNavSpacer);
            } else {
                initNavSpacer();
            }
        })();
    </script>
    @stack('scripts')
    @include('partials.prevent-fouc-end')
</body>
</html>
