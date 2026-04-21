<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class ServiceCategory extends Model
{
    use BelongsToTenant, HasFactory;
    protected $fillable = ['salon_id','business_type_id','name','slug','icon','color','text_color','description','sort_order','is_active'];
    protected $casts = ['is_active'=>'boolean'];

    protected static function booted(): void
    {
        static::saving(function (ServiceCategory $cat): void {
            if ($cat->salon_id === null || $cat->business_type_id === null) {
                return;
            }

            $salon = $cat->relationLoaded('salon')
                ? $cat->salon
                : Salon::query()->find($cat->salon_id);

            if ($salon === null) {
                return;
            }

            $allowed = $salon->businessTypes()->pluck('business_types.id')->map(fn ($id) => (int) $id)->all();
            if ($allowed === []) {
                $allowed = $salon->business_type_id ? [(int) $salon->business_type_id] : [];
            }

            if (! in_array((int) $cat->business_type_id, $allowed, true)) {
                throw ValidationException::withMessages([
                    'business_type_id' => ['Choose a business type that this location offers.'],
                ]);
            }
        });
    }

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function businessType(): BelongsTo
    {
        return $this->belongsTo(BusinessType::class, 'business_type_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'category_id')->orderBy('sort_order');
    }
}
