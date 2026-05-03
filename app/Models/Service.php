<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class Service extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;
    protected $fillable = [
        'salon_id','business_type_id','category_id','name','slug','description','image','duration_minutes',
        'buffer_minutes','price','price_from','price_on_consultation','deposit_type',
        'deposit_value','online_bookable','online_booking','show_in_menu','status','sort_order','color',
        'variants','addons','dynamic_pricing_enabled','staff_level','allowed_roles','service_location',
    ];
    protected $casts = [
        'price'=>'decimal:2','price_from'=>'decimal:2','deposit_value'=>'decimal:2',
        'online_bookable'=>'boolean','show_in_menu'=>'boolean','price_on_consultation'=>'boolean',
        'variants'=>'array','addons'=>'array','dynamic_pricing_enabled'=>'boolean',
        'allowed_roles' => 'array',
    ];
    public function salon()    { return $this->belongsTo(Salon::class); }

    public function businessType(): BelongsTo
    {
        return $this->belongsTo(BusinessType::class, 'business_type_id');
    }

    public function category() { return $this->belongsTo(ServiceCategory::class,'category_id'); }
    public function staff()    { return $this->belongsToMany(Staff::class,'service_staff')->withPivot('price_override')->withTimestamps(); }

    public function packages()
    {
        return $this->belongsToMany(ServicePackage::class, 'service_package_service')
            ->withPivot('sort_order')
            ->withTimestamps();
    }
    public function appointmentServices() { return $this->hasMany(AppointmentService::class); }
    protected $appends = ['is_active'];

    public function scopeActive($q) { return $q->where('status','active'); }
    public function scopeOnline($q) { return $q->where('online_bookable',true); }

    /** On-site only, or home visits too when the salon has enabled them for public booking. */
    public function scopeEligibleForPublicBooking($q, Salon $salon)
    {
        return $q->where(function ($q2) use ($salon): void {
            $q2->where('service_location', 'onsite');
            if ($salon->home_services_enabled) {
                $q2->orWhere('service_location', 'home');
            }
        });
    }

    public function isHomeService(): bool
    {
        return ($this->service_location ?? 'onsite') === 'home';
    }

    /** @return list<string> */
    public static function serviceLocationOptions(): array
    {
        return ['onsite', 'home'];
    }

    protected static function booted(): void
    {
        static::saving(function (Service $service): void {
            if ($service->category_id) {
                // Always resolve by id without tenant scope: multi-location uses session
                // active_salon while Tenant::current() may still be the domain salon, so a
                // scoped find() can return null even for valid categories on the selected location.
                $cat = ServiceCategory::withoutGlobalScopes()->find($service->category_id);

                if ($cat === null || (int) $cat->salon_id !== (int) $service->salon_id) {
                    throw ValidationException::withMessages([
                        'category_id' => ['Invalid category for this location.'],
                    ]);
                }

                $service->business_type_id = $cat->business_type_id;
            }

            if ($service->business_type_id === null) {
                throw ValidationException::withMessages([
                    'business_type_id' => ['Each service must be linked to a business type.'],
                ]);
            }

            $salon = $service->relationLoaded('salon')
                ? $service->salon
                : Salon::query()->find($service->salon_id);

            if ($salon === null) {
                return;
            }

            $allowed = $salon->businessTypes()->pluck('business_types.id')->map(fn ($id) => (int) $id)->all();
            if ($allowed === []) {
                $allowed = $salon->business_type_id ? [(int) $salon->business_type_id] : [];
            }

            if (! in_array((int) $service->business_type_id, $allowed, true)) {
                throw ValidationException::withMessages([
                    'business_type_id' => ['Choose a business type that this location offers.'],
                ]);
            }
        });
    }

    /** @return list<array{name: string, price: float|int}> */
    public function normalizedVariants(): array
    {
        $v = $this->variants;
        if (! is_array($v)) {
            return [];
        }

        return array_values(array_filter($v, fn ($row) => is_array($row) && trim((string) ($row['name'] ?? '')) !== ''));
    }

    /** @return list<array{name: string, price: float|int}> */
    public function normalizedAddons(): array
    {
        $v = $this->addons;
        if (! is_array($v)) {
            return [];
        }

        return array_values(array_filter($v, fn ($row) => is_array($row) && trim((string) ($row['name'] ?? '')) !== ''));
    }

    /**
     * Price + display name for one appointment line (variant + optional add-ons).
     *
     * @param  list<string>  $addonNames
     * @return array{
     *   price: float,
     *   service_name: string,
     *   duration_minutes: int,
     *   buffer_minutes: int,
     *   line_meta: array{variant: ?string, addons: list<array{name: string, price: float}>, base_unit_price: float, service_base_price: float}
     * }
     */
    public function computeAppointmentLine(?string $variantName, array $addonNames): array
    {
        $variantName = $variantName !== null ? trim($variantName) : null;
        if ($variantName === '') {
            $variantName = null;
        }

        $unit            = (float) $this->price;
        $matchedVariant  = null;

        foreach ($this->normalizedVariants() as $v) {
            if ($variantName !== null && strcasecmp((string) $v['name'], $variantName) === 0) {
                $unit           = (float) $v['price'];
                $matchedVariant = (string) $v['name'];
                break;
            }
        }

        $addonDetails = [];
        $addonTotal   = 0.0;

        foreach ($addonNames as $an) {
            $an = trim((string) $an);
            if ($an === '') {
                continue;
            }
            foreach ($this->normalizedAddons() as $ad) {
                if (strcasecmp((string) $ad['name'], $an) === 0) {
                    $p = (float) $ad['price'];
                    $addonTotal += $p;
                    $addonDetails[] = ['name' => (string) $ad['name'], 'price' => round($p, 2)];
                    break;
                }
            }
        }

        $displayName = $this->name;
        if ($matchedVariant) {
            $displayName .= ' (' . $matchedVariant . ')';
        }
        if ($addonDetails !== []) {
            $displayName .= ' +' . implode(', ', array_column($addonDetails, 'name'));
        }

        $linePrice = round($unit + $addonTotal, 2);
        $meta      = [
            'variant'            => $matchedVariant,
            'addons'             => $addonDetails,
            'base_unit_price'    => round($unit, 2),
            'service_base_price' => round((float) $this->price, 2),
        ];

        return [
            'price'              => $linePrice,
            'service_name'       => $displayName,
            'duration_minutes'   => (int) $this->duration_minutes,
            'buffer_minutes'     => (int) ($this->buffer_minutes ?? 0),
            'line_meta'          => $meta,
        ];
    }

    /**
     * Ordered appointment lines with totals (duration includes per-service buffer).
     *
     * @param  array<int>  $orderedServiceIds
     * @param  array<int, array{variant?: ?string, addons?: list<string>}>  $optionsByServiceId
     * @return array{
     *   total_duration_minutes: int,
     *   total_buffer_minutes: int,
     *   total_span_minutes: int,
     *   total_price: float,
     *   lines: list<array{service_id: int, service_name: string, duration_minutes: int, price: float, line_meta: array, sort_order: int}>
     * }
     */
    public static function summarizeForAppointment(int $salonId, array $orderedServiceIds, array $optionsByServiceId = []): array
    {
        if ($orderedServiceIds === []) {
            throw new InvalidArgumentException('No services selected.');
        }

        $unique = array_values(array_unique($orderedServiceIds));
        $map    = static::withoutTenantScope()
            ->where('salon_id', $salonId)
            ->whereIn('id', $unique)
            ->get()
            ->keyBy('id');

        if ($map->count() !== count($unique)) {
            throw new InvalidArgumentException('One or more services not found.');
        }

        $totalDuration = 0;
        $totalBuffer   = 0;
        $totalPrice    = 0.0;
        $lines         = [];
        $sort          = 0;

        foreach ($orderedServiceIds as $sid) {
            $service = $map->get($sid);
            if ($service === null) {
                throw new InvalidArgumentException('Invalid service in list.');
            }
            $opt     = $optionsByServiceId[$sid] ?? [];
            $variant = isset($opt['variant']) && $opt['variant'] !== '' ? trim((string) $opt['variant']) : null;
            $addons  = isset($opt['addons']) && is_array($opt['addons']) ? $opt['addons'] : [];

            $line = $service->computeAppointmentLine($variant, $addons);

            $totalDuration += $line['duration_minutes'];
            $totalBuffer += $line['buffer_minutes'];
            $totalPrice += $line['price'];

            $lines[] = [
                'service_id'       => (int) $service->id,
                'service_name'       => $line['service_name'],
                'duration_minutes'   => $line['duration_minutes'],
                'price'              => $line['price'],
                'line_meta'          => $line['line_meta'],
                'sort_order'         => $sort,
            ];
            $sort++;
        }

        return [
            'total_duration_minutes' => $totalDuration,
            'total_buffer_minutes'   => $totalBuffer,
            'total_span_minutes'     => $totalDuration + $totalBuffer,
            'total_price'            => round($totalPrice, 2),
            'lines'                  => $lines,
        ];
    }

    /**
     * @param  list<array{name?: string, price?: mixed}>|null  $rows
     * @return list<array{name: string, price: float}>|null
     */
    public static function normalizePriceRows(?array $rows): ?array
    {
        if ($rows === null || $rows === []) {
            return null;
        }

        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $out[] = [
                'name'  => $name,
                'price' => round((float) ($row['price'] ?? 0), 2),
            ];
        }

        return $out === [] ? null : $out;
    }

    /**
     * Parses lines like "Head Massage +200, Conditioning +₹300".
     *
     * @return list<array{name: string, price: float}>
     */
    public static function parseAddonsCommaText(?string $text): array
    {
        if ($text === null || trim($text) === '') {
            return [];
        }

        $parts = preg_split('/\s*,\s*/', $text) ?: [];
        $out   = [];

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            if (preg_match('/^(.+?)\s*\+\s*[₹£$]?\s*([\d.,]+)\s*$/u', $part, $m)) {
                $out[] = [
                    'name'  => trim($m[1]),
                    'price' => round((float) str_replace(',', '', $m[2]), 2),
                ];
            }
        }

        return $out;
    }

    /**
     * @param  list<array{name: string, price: float}>|null  $fromRows
     * @return list<array{name: string, price: float}>|null
     */
    public static function mergeAddonsFromText(?array $fromRows, ?string $text): ?array
    {
        $parsed = static::parseAddonsCommaText($text);
        $base   = $fromRows ?? [];
        if ($parsed === []) {
            return $base === [] ? null : $base;
        }

        $merged = array_merge($base, $parsed);

        return $merged === [] ? null : $merged;
    }

    /** Public URL for uploaded image (path is relative to the public disk). */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    /** @return list<string> */
    public static function supportedStaffRoles(): array
    {
        return ['owner', 'manager', 'stylist', 'therapist', 'receptionist', 'junior'];
    }

    /** @return list<string> */
    public function normalizedAllowedRoles(): array
    {
        $rows = is_array($this->allowed_roles) ? $this->allowed_roles : [];
        $valid = array_flip(static::supportedStaffRoles());
        $out = [];

        foreach ($rows as $row) {
            $role = strtolower(trim((string) $row));
            if ($role !== '' && isset($valid[$role])) {
                $out[$role] = true;
            }
        }

        return array_keys($out);
    }

    public function allowsStaffRole(?string $role): bool
    {
        $allowed = $this->normalizedAllowedRoles();
        if ($allowed === []) {
            return true;
        }

        $role = strtolower(trim((string) $role));

        return $role !== '' && in_array($role, $allowed, true);
    }

    public function setIsActiveAttribute($value): void
    {
        $this->attributes['status'] = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'active' : 'inactive';
    }

    protected static function newFactory()
    {
        return \Database\Factories\ServiceFactory::new();
    }

}
