<div class="space-y-6">
    <div class="rounded-2xl border border-velour-200/60 dark:border-velour-800/80 bg-velour-50/50 dark:bg-velour-950/20 px-5 py-4">
        <p class="text-sm text-body leading-relaxed">
            <strong class="text-heading">Loyalty plans</strong> are the options shown when you edit a client profile (Loyalty plan dropdown).
            Create tiers here with monthly pricing, service discounts, and benefits.
        </p>
    </div>

    <div class="flex flex-wrap justify-between gap-3 items-center">
        <p class="text-sm text-muted">Membership tiers assigned to clients for marketing counts and checkout discounts.</p>
        <button type="button" class="btn-primary btn-sm" x-on:click="openTier({ id: null, name: '', price_monthly: '', service_discount_percent: 0, benefits: '' })">+ Add loyalty plan</button>
    </div>

    @if($loyaltyTiers->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-800/30 px-8 py-12 text-center">
            <h3 class="text-lg font-semibold text-heading mb-2">No loyalty plans yet</h3>
            <p class="text-sm text-muted max-w-md mx-auto mb-6">Add a plan (e.g. Gold, Silver) so you can assign it to clients from the Clients page.</p>
            <button type="button" class="btn-primary" x-on:click="openTier({ id: null, name: '', price_monthly: '', service_discount_percent: 0, benefits: '' })">Create first plan</button>
        </div>
    @else
        <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($loyaltyTiers as $tier)
                <div class="card p-6 flex flex-col">
                    <h3 class="text-xl text-heading mb-1">{{ $tier->name }}</h3>
                    <p class="text-2xl font-bold text-heading">@money((float) $tier->price_monthly)<span class="text-sm font-normal text-muted">/mo</span></p>
                    <p class="text-sm text-muted mt-2">{{ $tier->member_count }} {{ Str::plural('client', $tier->member_count) }}</p>
                    @if($tier->service_discount_percent > 0)
                        <p class="text-xs font-semibold text-velour-700 dark:text-velour-300 mt-1">{{ $tier->service_discount_percent }}% off services</p>
                    @endif
                    <ul class="mt-4 space-y-2 text-sm text-body flex-1">
                        @foreach($tier->benefits ?? [] as $line)
                            <li class="flex gap-2 text-gray-700 dark:text-gray-200">
                                <span class="text-emerald-600 dark:text-emerald-400 shrink-0" aria-hidden="true">✓</span>
                                <span>{{ $line }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="flex flex-wrap gap-2 mt-6">
                        <button type="button" class="btn-primary btn-sm flex-1"
                                x-on:click="openTier({ id: {{ $tier->id }}, name: @json($tier->name), price_monthly: @json((string) $tier->price_monthly), service_discount_percent: {{ (int) $tier->service_discount_percent }}, benefits: @json(implode("\n", $tier->benefits ?? [])) })">Edit</button>
                        <a href="{{ route('marketing.loyalty.tiers.members', $tier) }}" class="btn-outline btn-sm flex-1 text-center">Members</a>
                        <form action="{{ route('marketing.loyalty.tiers.destroy', $tier) }}" method="POST" class="w-full"
                              onsubmit="return confirm('Remove this loyalty plan? Clients on this plan will be unassigned.');">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full btn-outline btn-sm text-red-600 dark:text-red-400 border-red-200 dark:border-red-900/60">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Tier modal --}}
    <x-modal-overlay show="tierModal !== null" x-on:click.self="tierModal=null">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl max-w-md w-full p-6 border border-gray-200 dark:border-gray-700" x-show="tierModal !== null">
            <form x-show="tierModal && tierModal.id != null" x-cloak :action="'{{ url('marketing/loyalty/tiers') }}/' + tierModal.id" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <h3 class="font-semibold text-heading text-lg">Edit loyalty plan</h3>
                <div><label class="form-label">Name</label><input type="text" name="name" x-model="tierModal.name" class="form-input w-full" required></div>
                <div><label class="form-label">Monthly price</label><input type="number" step="0.01" name="price_monthly" x-model="tierModal.price_monthly" class="form-input w-full" required></div>
                <div><label class="form-label">Service discount %</label><input type="number" name="service_discount_percent" min="0" max="100" x-model="tierModal.service_discount_percent" class="form-input w-full"></div>
                <div><label class="form-label">Benefits (one per line)</label><textarea name="benefits" rows="4" x-model="tierModal.benefits" class="form-textarea w-full"></textarea></div>
                <div class="flex gap-2 justify-end">
                    <button type="button" class="btn-outline" x-on:click="tierModal=null">Cancel</button>
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
            <form x-show="tierModal && tierModal.id === null" x-cloak action="{{ route('marketing.loyalty.tiers.store') }}" method="POST" class="space-y-4">
                @csrf
                <h3 class="font-semibold text-heading text-lg">New loyalty plan</h3>
                <div><label class="form-label">Name</label><input type="text" name="name" class="form-input w-full" required></div>
                <div><label class="form-label">Monthly price</label><input type="number" step="0.01" name="price_monthly" class="form-input w-full" required></div>
                <div><label class="form-label">Service discount %</label><input type="number" name="service_discount_percent" value="0" min="0" max="100" class="form-input w-full"></div>
                <div><label class="form-label">Benefits (one per line)</label><textarea name="benefits" rows="4" class="form-textarea w-full"></textarea></div>
                <div class="flex gap-2 justify-end">
                    <button type="button" class="btn-outline" x-on:click="tierModal=null">Cancel</button>
                    <button type="submit" class="btn-primary">Create</button>
                </div>
            </form>
        </div>
    </x-modal-overlay>
</div>
