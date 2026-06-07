<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientNote extends Model
{
    use SoftDeletes;
    protected $fillable = ['client_id','staff_id','type','content','is_pinned'];
    protected $casts = ['is_pinned'=>'boolean'];
    public function client() { return $this->belongsTo(Client::class); }
    public function staff()  { return $this->belongsTo(Staff::class); }
}
