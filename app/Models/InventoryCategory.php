<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class InventoryCategory extends Model
{
    use BelongsToTenant;
    protected $fillable = ['salon_id','name','slug','color','text_color','sort_order'];
    public function salon() { return $this->belongsTo(Salon::class); }
    public function items() { return $this->hasMany(InventoryItem::class,'category_id'); }
}
