<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    protected $fillable = ['inventory_item_id','staff_id','type','quantity_before','quantity_change','quantity_after','note','reference'];
    public function item()  { return $this->belongsTo(InventoryItem::class,'inventory_item_id'); }
    public function staff() { return $this->belongsTo(Staff::class); }
}
