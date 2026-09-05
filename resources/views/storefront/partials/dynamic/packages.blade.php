@php
    $salonData = $data['salon'] ?? [];
    $packages = $data['packages'] ?? [];
    $packageImages = \App\Support\StorefrontAssets::assets($theme)['packageImages'] ?? ['Rectangle 46.png', 'Rectangle 46 (1).png', 'Rectangle 27 (1).png'];
    $pkgCount = count($packages);
    $currencySymbol = \App\Helpers\CurrencyHelper::symbol($salon->currency ?? \App\Helpers\CurrencyHelper::defaultCode());
    $homeBookEnabled = (bool) (($data['salon']['online_booking_enabled'] ?? null) ?? ($salon->online_booking_enabled ?? true));
    $packageImageUrls = collect($packageImages)->map(fn ($img) => $asset($img))->values()->all();
    if ($pkgCount === 1) {
        $gridClass = 'is-single';
    } elseif ($pkgCount === 2) {
        $gridClass = 'is-multi-2 is-scroll';
    } else {
        $gridClass = 'is-multi-3 is-scroll';
    }
@endphp
@if($salonData && $pkgCount > 0)
<section id="packages"
         class="sf-home-packages"
         :class="{ 'has-sticky-selection': selectedPackageId !== null }"
         x-data="packagesSection(@js($packages), @js($packageImageUrls), @js($currencySymbol), @js($homeBookEnabled))">
    <div class="sf-home-packages__inner">
        <header class="sf-home-packages__header">
            <span class="sf-home-packages__eyebrow">Packages</span>
            <h2 class="sf-home-packages__title">Explore Our Packages</h2>
            <p class="sf-home-packages__subtitle">Save more with our curated service packages.</p>
        </header>

        <div class="sf-home-packages__grid {{ $gridClass }}">
            <template x-for="(pkg, index) in packages" :key="pkg.id">
                <article role="button" tabindex="0"
                         x-data="{ expanded: false }"
                         :data-home-package="pkg.id"
                         @click="togglePackage(pkg.id)"
                         @keydown.enter.prevent="togglePackage(pkg.id)"
                         @keydown.space.prevent="togglePackage(pkg.id)"
                         :class="isPicked(pkg.id) ? 'is-selected' : ''"
                         class="sf-home-packages__card">
                    <div class="sf-home-packages__image-wrap">
                        <img :src="packageImage(index)" :alt="pkg.name" draggable="false">
                        <span class="sf-home-packages__badge sf-home-packages__badge--featured"
                              x-show="pkg.badge_label"
                              x-text="pkg.badge_label"
                              x-cloak></span>
                        <span class="sf-home-packages__badge sf-home-packages__badge--save"
                              x-show="pkg.save_badge"
                              x-text="pkg.save_badge"
                              x-cloak></span>
                    </div>
                    <div class="sf-home-packages__body">
                        <h3 class="sf-home-packages__name" x-text="pkg.name"></h3>
                        <p class="sf-home-packages__desc"
                           x-show="packageDescription(pkg.description)"
                           x-text="packageDescription(pkg.description)"></p>
                        <p class="sf-home-packages__meta" x-text="packageMeta(pkg)"></p>

                        <ul class="sf-home-packages__items">
                            <template x-for="item in visibleItems(pkg, expanded)" :key="item.name + '-' + item.price">
                                <li class="sf-home-packages__item">
                                    <span class="sf-home-packages__item-name" x-text="item.name"></span>
                                    <span class="sf-home-packages__item-price" x-text="item.price"></span>
                                </li>
                            </template>
                        </ul>
                        <button type="button"
                                class="sf-home-packages__more"
                                x-show="hiddenCount(pkg) > 0 && !expanded"
                                @click.stop="expanded = true"
                                x-text="'+' + hiddenCount(pkg) + ' more service' + (hiddenCount(pkg) === 1 ? '' : 's')"></button>
                        <button type="button"
                                class="sf-home-packages__more"
                                x-show="hiddenCount(pkg) > 0 && expanded"
                                @click.stop="expanded = false">Show less</button>

                        <div class="sf-home-packages__pricing">
                            <div class="sf-home-packages__value-row"
                                 x-show="Number(pkg.components_total) > 0"
                                 :class="pkg.has_savings ? 'is-struck' : ''"
                                 x-cloak>
                                <span>Individual value</span>
                                <span x-text="pkg.components_formatted"></span>
                            </div>
                            <div class="sf-home-packages__value-row">
                                <span>Package price</span>
                                <span class="sf-home-packages__price" x-text="pkg.price_formatted"></span>
                            </div>
                            <div class="sf-home-packages__save-row" x-show="pkg.has_savings" x-cloak>
                                <span class="sf-home-packages__save"
                                      x-text="'SAVE ' + pkg.savings_formatted + ' • ' + pkg.savings_percent + '% OFF'"></span>
                            </div>
                        </div>

                        <span data-home-package-label class="sf-home-packages__select">Select package →</span>
                    </div>
                </article>
            </template>
        </div>

        <div class="sf-home-packages__cta" x-show="selectedPackageId !== null" x-cloak>
            <p class="sf-home-packages__cta-summary" x-text="selectionSummary"></p>
            @if($homeBookEnabled)
            <button type="button" @click="bookSelected()" class="sf-home-services__cta-btn">
                Continue to booking →
            </button>
            @endif
        </div>
    </div>

    <div class="sf-home-services__sticky" x-show="selectedPackageId !== null && bookingEnabled" x-cloak>
        <div class="sf-home-services__sticky-inner">
            <div class="sf-home-services__sticky-summary min-w-0">
                <div class="sf-home-services__sticky-text" x-text="stickyTitle"></div>
                <div class="sf-home-services__sticky-meta" x-text="stickyMeta"></div>
            </div>
            <div class="sf-home-services__sticky-price" x-text="stickyPrice"></div>
            <button type="button" @click="bookSelected()" class="sf-home-services__cta-btn sf-home-services__sticky-btn">
                Continue →
            </button>
        </div>
    </div>
</section>
@endif

@once
@push('scripts')
<script>
function packagesSection(packages, packageImageUrls, currencySymbol, bookingEnabled) {
    return {
        packages,
        packageImageUrls,
        currencySymbol,
        bookingEnabled,
        selectedPackageId: null,
        get selectedPackage() {
            if (this.selectedPackageId === null) return null;
            return this.packages.find(p => Number(p.id) === Number(this.selectedPackageId)) ?? null;
        },
        get selectionSummary() {
            const pkg = this.selectedPackage;
            if (!pkg) return '';
            return 'Package selected • ' + pkg.price_formatted;
        },
        get stickyTitle() {
            return this.selectedPackage?.name ?? '';
        },
        get stickyMeta() {
            const pkg = this.selectedPackage;
            if (!pkg) return '';
            const parts = [];
            if (pkg.service_count) parts.push(pkg.service_count + ' services');
            if (pkg.duration_formatted) parts.push(pkg.duration_formatted);
            return parts.join(' • ');
        },
        get stickyPrice() {
            return this.selectedPackage?.price_formatted ?? '';
        },
        packageDescription(text) {
            const t = String(text || '').trim();
            if (t.length < 4) return '';
            if (/^(dfd|test|asdf|xxx|abc|demo|sample|lorem|qwerty|na|n\/a)$/i.test(t)) return '';
            return t;
        },
        packageMeta(pkg) {
            const parts = [];
            const n = pkg.service_count || (pkg.items || []).length;
            if (n) parts.push(n + ' service' + (n === 1 ? '' : 's'));
            if (pkg.duration_formatted) parts.push('Approx. ' + pkg.duration_formatted);
            return parts.join(' • ');
        },
        showIndividualValue(pkg) {
            return Number(pkg.components_total) > 0;
        },
        visibleItems(pkg, expanded) {
            const items = pkg.items || [];
            if (expanded) return items;
            return items.slice(0, 3);
        },
        hiddenCount(pkg) {
            return Math.max(0, (pkg.items || []).length - 3);
        },
        packageImage(index) {
            const urls = this.packageImageUrls || [];
            if (!urls.length) return '';
            return urls[index % urls.length];
        },
        isPicked(id) {
            return Number(this.selectedPackageId) === Number(id);
        },
        togglePackage(id) {
            if (window.storefrontHomeCart) window.storefrontHomeCart.togglePackage(id);
            const snap = window.storefrontHomeCart ? window.storefrontHomeCart.snapshot() : { packageIds: [] };
            this.selectedPackageId = snap.packageIds.length ? snap.packageIds[0] : null;
        },
        bookSelected() {
            if (!this.bookingEnabled) return;
            if (window.storefrontHomeCart) window.storefrontHomeCart.bookSelected();
            else window.location.hash = 'book';
        },
        init() {
            this._onCart = (e) => {
                const ids = e.detail?.packageIds ?? [];
                this.selectedPackageId = ids.length ? ids[0] : null;
            };
            window.addEventListener('storefront-home-cart-change', this._onCart);
        },
        destroy() {
            if (this._onCart) window.removeEventListener('storefront-home-cart-change', this._onCart);
        },
    };
}
</script>
@endpush
@endonce
