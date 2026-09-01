<?php
namespace App\Models;
use App\Traits\BelongsToTenant;

use App\Traits\AuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use AuditLog, BelongsToTenant;
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'salon_id','category_id','name','sku','barcode','supplier','unit',
        'cost_price','retail_price','stock_quantity','quantity','min_stock_level',
        'reorder_quantity','type','image','notes','last_ordered_at','is_active',
    ];

    protected $appends = ['quantity', 'low_stock_threshold'];
    protected $casts = [
        'cost_price'=>'decimal:2','retail_price'=>'decimal:2',
        'is_active'=>'boolean','last_ordered_at'=>'date',
    ];
    public function salon()       { return $this->belongsTo(Salon::class); }
    public function category()    { return $this->belongsTo(InventoryCategory::class,'category_id'); }
    public function adjustments() { return $this->hasMany(InventoryAdjustment::class); }
    public function getIsLowStockAttribute(): bool { return $this->stock_quantity < $this->min_stock_level; }
    public function getMarginPercentAttribute(): float {
        if (!$this->retail_price || !$this->cost_price) return 0;
        return round((($this->retail_price - $this->cost_price) / $this->retail_price) * 100, 1);
    }

    public function getLowStockThresholdAttribute(): int
    {
        return $this->min_stock_level;
    }

    public function setLowStockThresholdAttribute(int $value): void
    {
        $this->attributes['min_stock_level'] = $value;
    }

    public function scopeLowStock($q) { return $q->whereColumn('stock_quantity','<','min_stock_level'); }
    public function scopeRetail($q)   { return $q->whereIn('type',['retail','both']); }

    /** Matches {@see stockStatusLevel()} === low (not critical). */
    public function scopeWhereStockTierLow($query)
    {
        return $query->where('min_stock_level', '>', 0)
            ->whereColumn('stock_quantity', '<', 'min_stock_level')
            ->where('stock_quantity', '>', 0)
            ->whereRaw('stock_quantity > FLOOR(min_stock_level / 2)');
    }

    /** Matches {@see stockStatusLevel()} === critical. */
    public function scopeWhereStockTierCritical($query)
    {
        return $query->where('min_stock_level', '>', 0)
            ->whereColumn('stock_quantity', '<', 'min_stock_level')
            ->where(function ($q) {
                $q->where('stock_quantity', '=', 0)
                    ->orWhereRaw('stock_quantity <= FLOOR(min_stock_level / 2)');
            });
    }

    public function getQuantityAttribute(): ?int
    {
        // Support partial selects that alias stock_quantity → quantity (avoid strict missing-attribute errors).
        if (array_key_exists('quantity', $this->attributes)) {
            return (int) $this->attributes['quantity'];
        }
        if (array_key_exists('stock_quantity', $this->attributes)) {
            return (int) $this->attributes['stock_quantity'];
        }

        return null;
    }

    public function setQuantityAttribute($value): void
    {
        $this->attributes['stock_quantity'] = $value;
    }
    public function scopePro($q)      { return $q->whereIn('type',['professional','both']); }

    /** In stock vs low vs critical (UI / alerts). Critical = well below minimum or out of stock. */
    public static function stockStatusLevelFrom(int $stock, int $min): string
    {
        if ($min <= 0) {
            return 'in_stock';
        }
        if ($stock >= $min) {
            return 'in_stock';
        }
        if ($stock === 0 || $stock <= intdiv($min, 2)) {
            return 'critical';
        }

        return 'low';
    }

    public function stockStatusLevel(): string
    {
        return self::stockStatusLevelFrom((int) $this->stock_quantity, (int) $this->min_stock_level);
    }
}
