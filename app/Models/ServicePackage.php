<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicePackage extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'salon_id',
        'name',
        'slug',
        'description',
        'price',
        'online_bookable',
        'status',
        'sort_order',
        'allowed_roles',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'online_bookable' => 'boolean',
        'allowed_roles' => 'array',
    ];

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_package_service')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOnline($query)
    {
        return $query->where('online_bookable', true);
    }

    /** @return list<int> */
    public function orderedServiceIds(): array
    {
        return $this->services()->pluck('services.id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * Combined appointment span (minutes) for all services in this package, in pivot order.
     * Use for booking/slot logic when expanding a package into service lines.
     */
    public function totalSpanMinutesForAppointment(): int
    {
        $ids = $this->orderedServiceIds();
        if ($ids === []) {
            return 0;
        }

        $snapshot = Service::summarizeForAppointment((int) $this->salon_id, $ids, []);

        return (int) $snapshot['total_span_minutes'];
    }

    /** Sum of standalone service prices (for comparison / savings display). */
    public function componentsTotalPrice(): float
    {
        return round((float) $this->services->sum(fn (Service $s) => (float) $s->price), 2);
    }

    /** @return list<string> */
    public function normalizedAllowedRoles(): array
    {
        $rows = is_array($this->allowed_roles) ? $this->allowed_roles : [];
        $valid = array_flip(Service::supportedStaffRoles());
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

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }
}
