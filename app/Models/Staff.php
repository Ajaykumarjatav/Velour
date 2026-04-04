<?php
namespace App\Models;
use App\Traits\BelongsToTenant;

use App\Traits\AuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Staff extends Model
{
    use AuditLog, BelongsToTenant;
    use HasFactory, SoftDeletes;

    // Expose a virtual `name` attribute (first_name + last_name) for convenience.
    // This is especially useful when the UI expects `name` but the DB stores
    // first/last parts separately.
    protected $appends = ['name'];

    protected $fillable = [
        'salon_id','user_id','first_name','last_name','email','phone',
        'avatar','initials','color','role','bio','specialisms','commission_rate','base_salary',
        'access_level','start_time','end_time','working_days','hired_at',
        'is_active','bookable_online','sort_order',
    ];
    protected $casts = [
        'specialisms'=>'array','working_days'=>'array',
        'is_active'=>'boolean','bookable_online'=>'boolean',
        'commission_rate'=>'decimal:2','base_salary'=>'decimal:2','hired_at'=>'date',
    ];
    // Virtual 'name' attribute so controllers/views can use $staff->name
    public function getNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Scope a query to select a `name` column (concatenated first/last).
     *
     * This helps avoid SQL errors when code expects a `name` column.
     */
    public function scopeWithName($query)
    {
        return $query->select([
            'id',
            'first_name',
            'last_name',
            'color',
            DB::raw("CONCAT(first_name, ' ', last_name) as name"),
        ]);
    }

    // Allow $staff->name = 'John Smith' → splits automatically
    public function setNameAttribute(string $value): void
    {
        $parts = explode(' ', trim($value), 2);
        $this->attributes['first_name'] = $parts[0];
        $this->attributes['last_name']  = $parts[1] ?? '';
    }

    public function getFullNameAttribute(): string { return $this->name; }
    public function salon()        { return $this->belongsTo(Salon::class); }
    public function user()         { return $this->belongsTo(User::class); }
    public function services()     { return $this->belongsToMany(Service::class,'service_staff')->withPivot('price_override')->withTimestamps(); }
    public function appointments() { return $this->hasMany(Appointment::class); }
    public function leaveRequests() { return $this->hasMany(StaffLeaveRequest::class); }
    public function reviews()        { return $this->hasMany(Review::class); }
    public function adjustments()  { return $this->hasMany(InventoryAdjustment::class); }
    protected static function newFactory()
    {
        return \Database\Factories\StaffFactory::new();
    }

}
