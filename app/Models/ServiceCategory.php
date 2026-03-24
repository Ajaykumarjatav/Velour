<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use BelongsToTenant, HasFactory;
    protected $fillable = ['salon_id','name','slug','icon','color','text_color','description','sort_order','is_active'];
    protected $casts = ['is_active'=>'boolean'];
    public function salon()    { return $this->belongsTo(Salon::class); }
    public function services() { return $this->hasMany(Service::class,'category_id')->orderBy('sort_order'); }
}
