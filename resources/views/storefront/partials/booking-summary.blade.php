<aside class="sf-booking-summary" aria-label="Booking summary">
    <div class="sf-booking-summary-inner">
        <h2 class="sf-booking-summary-title" x-text="summaryHeading()"></h2>

        <template x-if="summaryLines().length === 0">
            <p class="sf-booking-summary-empty">Select services to start your booking.</p>
        </template>

        <ul x-show="summaryLines().length > 0" class="sf-booking-summary-list">
            <template x-for="line in summaryLines()" :key="line.key">
                <li class="sf-booking-summary-item">
                    <div class="sf-booking-summary-item-main">
                        <span class="sf-booking-summary-check" aria-hidden="true">✓</span>
                        <div class="min-w-0">
                            <p class="sf-booking-summary-item-name" x-text="line.name"></p>
                            <p class="sf-booking-summary-item-meta" x-text="(line.duration || 0) + ' min'"></p>
                        </div>
                    </div>
                    <span class="sf-booking-summary-item-price tabular-nums" x-text="formatPrice(line.price)"></span>
                </li>
            </template>
        </ul>

        <template x-if="step >= 2">
            <div class="sf-booking-summary-extra">
                <p class="sf-booking-summary-extra-label">Stylist</p>
                <p class="sf-booking-summary-extra-value" x-text="staffDisplayName()"></p>
            </div>
        </template>

        <template x-if="step >= 2 && selected.date">
            <div class="sf-booking-summary-extra">
                <p class="sf-booking-summary-extra-label">Date</p>
                <p class="sf-booking-summary-extra-value" x-text="formatDateShort(selected.date)"></p>
            </div>
        </template>

        <template x-if="step >= 2 && selected.slot">
            <div class="sf-booking-summary-extra">
                <p class="sf-booking-summary-extra-label">Time</p>
                <p class="sf-booking-summary-extra-value" x-text="selected.slot.time"></p>
            </div>
        </template>

        <div x-show="summaryLines().length > 0" class="sf-booking-summary-total">
            <span>Total</span>
            <span class="tabular-nums font-bold" x-text="formatPrice(totalPrice())"></span>
        </div>

        <button type="button"
                x-show="summaryCanContinue()"
                @click="summaryContinue()"
                :disabled="step === 4 && confirming"
                class="sf-booking-summary-cta">
            <span x-text="summaryContinueLabel()"></span>
            <span aria-hidden="true">→</span>
        </button>

        <p x-show="step === 1 && summaryLines().length > 0" class="sf-booking-summary-hint">
            Tap a stylist on the left to continue.
        </p>
    </div>
</aside>

<div x-show="summaryLines().length > 0 && summaryCanContinue()" x-cloak
     class="sf-booking-mobile-bar lg:hidden">
    <div class="sf-booking-mobile-bar-inner">
        <div class="min-w-0">
            <p class="text-sm font-medium text-white truncate" x-text="selectedCountLabel()"></p>
            <p class="text-xs text-white/55 tabular-nums" x-text="formatPrice(totalPrice())"></p>
        </div>
        <button type="button" @click="summaryContinue()" class="sf-booking-mobile-cta">
            <span x-text="summaryContinueLabel()"></span>
            <span aria-hidden="true">→</span>
        </button>
    </div>
</div>
