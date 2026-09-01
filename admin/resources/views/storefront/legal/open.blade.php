@php
    $storefrontHome = $p['Website URL'] ?? \App\Support\StorefrontUrl::website($salon);
@endphp
@include('storefront.themes.'.$theme.'.partials.top-bar')

<main class="w-full bg-[#F5F0EA]">
    <div class="max-w-[820px] mx-auto px-4 sm:px-6 py-10 sm:py-14">
        <article class="legal-article bg-white rounded-2xl border border-black/5 shadow-[0_12px_40px_rgba(42,31,26,0.08)] px-5 py-8 sm:px-10 sm:py-12">
            @once
            @push('head')
            <style>
                .legal-article { color: #3f3a36; }
                .legal-article h1 {
                    font-family: Manrope, sans-serif;
                    font-weight: 800;
                    font-size: clamp(1.85rem, 4vw, 2.5rem);
                    line-height: 1.15;
                    letter-spacing: -0.03em;
                    margin: 0 0 0.75rem;
                    color: #1c1917;
                }
                .legal-meta {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 0.5rem 1rem;
                    align-items: center;
                    font-size: 0.8125rem;
                    color: #78716c;
                    margin: 0 0 1.75rem;
                    padding-bottom: 1.25rem;
                    border-bottom: 1px solid #e7e5e4;
                }
                .legal-meta span {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.35rem;
                }
                .legal-article h2 {
                    font-family: Manrope, sans-serif;
                    font-weight: 800;
                    font-size: 1.125rem;
                    line-height: 1.35;
                    margin: 2.25rem 0 0.85rem;
                    padding-top: 1.5rem;
                    border-top: 1px solid #f0ece8;
                    color: #1c1917;
                }
                .legal-article h2:first-of-type {
                    border-top: 0;
                    padding-top: 0;
                    margin-top: 0.5rem;
                }
                .legal-article p {
                    margin: 0 0 1rem;
                    line-height: 1.8;
                    color: #4a4540;
                }
                .legal-article ul {
                    margin: 0 0 1.1rem;
                    padding-left: 1.2rem;
                    color: #4a4540;
                }
                .legal-article li {
                    margin-bottom: 0.4rem;
                    line-height: 1.7;
                }
                .legal-article a {
                    color: #8b4d32;
                    text-decoration: underline;
                    text-underline-offset: 2px;
                    word-break: break-word;
                }
                .legal-dl {
                    margin: 0 0 1.25rem;
                    border: 1px solid #efeae4;
                    border-radius: 0.9rem;
                    overflow: hidden;
                    background: #faf8f6;
                }
                .legal-dl > div {
                    display: grid;
                    grid-template-columns: 10.5rem minmax(0, 1fr);
                    gap: 0.35rem 1rem;
                    padding: 0.85rem 1rem;
                    border-bottom: 1px solid #efeae4;
                }
                .legal-dl > div:last-child { border-bottom: 0; }
                .legal-dl dt {
                    font-size: 0.75rem;
                    font-weight: 700;
                    letter-spacing: 0.04em;
                    text-transform: uppercase;
                    color: #8a8178;
                    padding-top: 0.15rem;
                }
                .legal-dl dd { margin: 0; color: #1c1917; font-weight: 500; }
                @media (max-width: 640px) {
                    .legal-dl > div { grid-template-columns: 1fr; }
                }
                .legal-notice {
                    margin-top: 2.25rem;
                    padding: 1.35rem 1.4rem;
                    border-radius: 1rem;
                    background: #f7f2ec;
                    border: 1px solid #eadfd4;
                }
                .legal-notice h2 {
                    margin-top: 0;
                    padding-top: 0;
                    border-top: 0;
                }
                .legal-signoff { margin-bottom: 0; color: #1c1917; }
            </style>
            @endpush
            @endonce
