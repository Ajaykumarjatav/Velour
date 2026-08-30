<aside class="sf-booking-summary" aria-label="Booking summary">
    <div class="sf-booking-summary-inner">
        <h2 class="sf-booking-summary-title" x-text="summaryHeading()"></h2>

        <template x-if="summaryLines().length === 0">
            <p class="sf-booking-summary-empty">Select services to start your booking.</p>
        </template>

        <ul x-show="summaryLines().length > 0" class="sf-booking-summary-list">
            <template x-for="line in summaryLines()" :key="line.key">
                <li class="sf-booking-summary-item" :class="line.type === 'package' ? 'is-package' : ''">
                    <div class="sf-booking-summary-item-head">
                        <div class="sf-booking-summary-item-main">
                            <span class="sf-booking-summary-check" aria-hidden="true">✓</span>
                            <div class="min-w-0">
                                <p class="sf-booking-summary-item-name" x-text="line.name"></p>
                                <p class="sf-booking-summary-item-meta"
                                   x-text="line.type === 'package'
                                       ? (line.serviceCount + ' service' + (line.serviceCount === 1 ? '' : 's') + ' • ' + formatDurationFriendly(line.duration))
                                       : formatDurationFriendly(line.duration)"></p>
                            </div>
                        </div>
                        <span class="sf-booking-summary-item-price tabular-nums" x-text="formatPrice(line.price)"></span>
                    </div>

                    <template x-if="line.type === 'package' && line.services.length > 0">
                        <div class="sf-booking-summary-package">
                            <button type="button"
                                    @click="packageDetailsOpen = !packageDetailsOpen"
                                    class="sf-booking-summary-package-toggle">
                                <span x-text="packageDetailsOpen ? 'Hide included services' : 'View included services'"></span>
                                <span aria-hidden="true" x-text="packageDetailsOpen ? '▴' : '▾'"></span>
                            </button>
                            <ul x-show="packageDetailsOpen" x-cloak class="sf-booking-summary-package-list">
                                <template x-for="svc in line.services" :key="line.key + '-svc-' + svc.id">
                                    <li class="sf-booking-summary-package-line">
                                        <span x-text="'✓ ' + svc.name"></span>
                                        <span class="tabular-nums" x-text="formatPrice(svc.price)"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>
                </li>
            </template>
        </ul>

        <p x-show="summaryLines().length > 0 && summaryTotalDuration() > 0"
           class="sf-booking-summary-duration"
           x-text="'Total duration • ' + formatDurationFriendly(summaryTotalDuration())"></p>

        <div x-show="summaryLines().length > 0" class="sf-booking-summary-details">
            <template x-if="step >= 1 && selected.staffChoiceMade">
                <div class="sf-booking-summary-extra">
                    <p class="sf-booking-summary-extra-label">✂ Stylist</p>
                    <p class="sf-booking-summary-extra-value is-highlight" x-text="staffDisplayName()"></p>
                </div>
            </template>

            <template x-if="step >= 2 && selected.date">
                <div class="sf-booking-summary-extra">
                    <p class="sf-booking-summary-extra-label">📅 Date</p>
                    <p class="sf-booking-summary-extra-value is-highlight" x-text="formatDateShort(selected.date)"></p>
                </div>
            </template>

            <template x-if="step >= 2 && selected.slot">
                <div class="sf-booking-summary-extra">
                    <p class="sf-booking-summary-extra-label">🕐 Time</p>
                    <p class="sf-booking-summary-extra-value is-highlight" x-text="formatTimeDisplay(selected.slot.time)"></p>
                </div>
            </template>
        </div>

        <div x-show="summaryLines().length > 0" class="sf-booking-summary-total">
            <span>Total</span>
            <span class="tabular-nums font-bold" x-text="formatPrice(totalPrice())"></span>
        </div>

        <button type="button"
                x-show="summaryLines().length > 0"
                @click="summaryCanContinue() && summaryContinue()"
                :disabled="!summaryCanContinue() || (step === 4 && confirming)"
                class="sf-booking-summary-cta"
                :class="{ 'is-disabled': !summaryCanContinue(), 'is-ready': summaryCanContinue() }">
            <span x-text="summaryContinueLabel()"></span>
            <span aria-hidden="true">→</span>
        </button>

        <p x-show="step === 1 && summaryLines().length > 0 && !selected.staffChoiceMade"
           class="sf-booking-summary-hint">
            Select a stylist to continue.
        </p>
        <p x-show="step === 2 && summaryLines().length > 0 && !selected.slot"
           class="sf-booking-summary-hint">
            Select a time to continue.
        </p>
    </div>
</aside>

<div x-show="summaryLines().length > 0" x-cloak class="sf-booking-mobile-bar lg:hidden">
    <div class="sf-booking-mobile-bar-inner">
        <div class="min-w-0">
            <p class="text-sm font-medium text-white truncate"
               x-text="step === 1 && !selected.staffChoiceMade ? 'Choose a stylist' : selectedCountLabel()"></p>
            <p class="text-xs text-white/55 tabular-nums" x-text="formatPrice(totalPrice())"></p>
        </div>
        <button type="button"
                @click="summaryCanContinue() && summaryContinue()"
                :disabled="!summaryCanContinue() || (step === 4 && confirming)"
                class="sf-booking-mobile-cta"
                :class="{ 'is-disabled': !summaryCanContinue(), 'is-ready': summaryCanContinue() }">
            <span x-text="summaryContinueLabel()"></span>
            <span aria-hidden="true">→</span>
        </button>
    </div>
</div>
