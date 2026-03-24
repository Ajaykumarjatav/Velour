<?php

namespace App\Models;

use Spatie\Multitenancy\Models\Tenant as SpatieTenant;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Tenant
 *
 * The Tenant model doubles as the Salon — the `salons` table is the single
 * source of truth for both application data (name, address, settings…) and
 * multitenancy resolution (domain, subdomain).
 *
 * Extending Spatie\Multitenancy\Models\Tenant gives us:
 *   • static Tenant::current()        — returns the active tenant or null
 *   • $tenant->makeCurrent()          — sets this tenant as current
 *   • static Tenant::checkCurrent()   — throws if no tenant active
 *   • $tenant->forget()               — unsets current tenant
 *   • static Tenant::all()            — landlord-scope query (no global scope)
 *
 * NOTE: Because Tenant extends the base Spatie model (not our Salon model) we
 * intentionally keep this class thin and delegate all "business" relations to
 * App\Models\Salon.  The two classes share the same DB row; use whichever
 * is more convenient in context.
 */
class Tenant extends SpatieTenant
{
    use HasFactory, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Table & Key
    |--------------------------------------------------------------------------
    |
    | Reuse the existing `salons` table — no new table required.
    |
    */

    protected $table = 'salons';

    protected $primaryKey = 'id';

    /*
    |--------------------------------------------------------------------------
    | Mass-Assignable Attributes
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'domain',       // Custom domain:   bookings.mysalon.com
        'subdomain',    // Velour subdomain: mysalon  (→ mysalon.velour.app)
        'description',
        'phone',
        'email',
        'website',
        'address_line1',
        'address_line2',
        'city',
        'county',
        'postcode',
        'country',
        'latitude',
        'longitude',
        'timezone',
        'currency',
        'locale',
        'logo',
        'cover_image',
        'social_links',
        'booking_url',
        'google_place_id',
        'stripe_account_id',
        'online_booking_enabled',
        'new_client_booking_enabled',
        'deposit_required',
        'deposit_percentage',
        'instant_confirmation',
        'booking_advance_days',
        'cancellation_hours',
        'opening_hours',
        'is_active',
    ];

    protected $casts = [
        'social_links'               => 'array',
        'opening_hours'              => 'array',
        'online_booking_enabled'     => 'boolean',
        'new_client_booking_enabled' => 'boolean',
        'deposit_required'           => 'boolean',
        'instant_confirmation'       => 'boolean',
        'is_active'                  => 'boolean',
        'deposit_percentage'         => 'decimal:2',
        'latitude'                   => 'decimal:7',
        'longitude'                  => 'decimal:7',
    ];

    /*
    |--------------------------------------------------------------------------
    | Tenant Resolution Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Return the full subdomain URL for this tenant.
     *
     *   mysalon → https://mysalon.velour.app
     */
    public function subdomainUrl(): string
    {
        $base = config('app.base_domain', 'velour.app');
        return 'https://' . $this->subdomain . '.' . $base;
    }

    /**
     * Return the active booking URL — custom domain if configured,
     * otherwise the subdomain URL.
     */
    public function publicBookingUrl(): string
    {
        return $this->domain
            ? 'https://' . $this->domain
            : $this->subdomainUrl();
    }

    /*
    |--------------------------------------------------------------------------
    | Relations (mirrored from Salon for convenience)
    |--------------------------------------------------------------------------
    */

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function staff()
    {
        return $this->hasMany(Staff::class, 'salon_id');
    }

    public function clients()
    {
        return $this->hasMany(Client::class, 'salon_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'salon_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'salon_id');
    }

    public function settings()
    {
        return $this->hasMany(SalonSetting::class, 'salon_id');
    }

    /**
     * Convenience helper to read a single setting value.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        $setting = $this->settings()->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}
